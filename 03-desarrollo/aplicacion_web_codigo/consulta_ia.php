<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'config/gemini.php';
require_once 'includes/ia.php';

// Esta página exige sesión activa
requireLogin();

$idUsuario = $_SESSION['id_usuario'];

$pregunta = '';
$respuesta = '';
$documentosUsados = [];
$error = '';

// =====================================================
// Buscar en la base de datos los documentos ya procesados
// que sean relevantes para la pregunta (recuperación / "R" de RAG).
//
// Búsqueda simple por coincidencia de palabras en el resumen y el texto
// extraído. Solo se consideran documentos con estado_procesamiento =
// 'procesado', porque son los únicos que ya tienen resumen/texto en
// analisis_documento.
// =====================================================
function buscarDocumentosRelevantes(PDO $pdo, string $pregunta, int $maximo = 5): array
{
    // Palabras significativas de la pregunta (se ignoran las muy cortas,
    // como "de", "el", "la", para no ensuciar la búsqueda).
    $palabras = preg_split('/\s+/', mb_strtolower($pregunta));
    $palabras = array_filter($palabras, fn($p) => mb_strlen($p) >= 4);
    $palabras = array_slice(array_unique($palabras), 0, 6);

    if (empty($palabras)) {
        return [];
    }

    $palabras = array_values($palabras); // reindexar para evitar huecos en las llaves

    $condicionesBusqueda = [];
    $condicionesRelevancia = [];
    $params = [];

    foreach ($palabras as $i => $palabra) {
        $valor = "%{$palabra}%";

        // Cada aparición del parámetro necesita su propio nombre único,
        // porque con PDO::ATTR_EMULATE_PREPARES => false (prepares nativos
        // de MySQL) no se puede repetir el mismo :nombre varias veces en
        // la misma consulta.
        $pResumen = ":resumen{$i}";
        $pTexto1  = ":texto{$i}a";
        $pTexto2  = ":texto{$i}b";

        $condicionesBusqueda[] = "(a.resumen LIKE {$pResumen} OR a.texto_extraido LIKE {$pTexto1})";
        $condicionesRelevancia[] = "(CASE WHEN a.texto_extraido LIKE {$pTexto2} THEN 1 ELSE 0 END)";

        $params[$pResumen] = $valor;
        $params[$pTexto1] = $valor;
        $params[$pTexto2] = $valor;
    }

    $sumaRelevancia = implode(' + ', $condicionesRelevancia);

    $sql = "SELECT d.nombre_original, a.resumen, a.texto_extraido,
                   ({$sumaRelevancia}) AS relevancia
            FROM documentos d
            JOIN analisis_documento a ON a.id_documento = d.id_documento
            WHERE d.estado_procesamiento = 'procesado'
              AND (" . implode(' OR ', $condicionesBusqueda) . ")
            ORDER BY relevancia DESC
            LIMIT {$maximo}";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// =====================================================
// Procesar la pregunta (POST)
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pregunta'])) {
    $pregunta = trim($_POST['pregunta']);

    if ($pregunta === '') {
        $error = 'Escribe una pregunta.';
    } else {
        $documentosRelevantes = buscarDocumentosRelevantes($pdo, $pregunta);

        // Se arma el contexto para la IA, truncando cada documento para no
        // exceder el límite de tokens de la API en una sola pregunta.
        $LIMITE_POR_DOCUMENTO = 6000;
        $contexto = array_map(function ($doc) use ($LIMITE_POR_DOCUMENTO) {
            return [
                'nombre' => $doc['nombre_original'],
                'resumen' => $doc['resumen'],
                'texto' => mb_substr($doc['texto_extraido'] ?? '', 0, $LIMITE_POR_DOCUMENTO),
            ];
        }, $documentosRelevantes);

        $resultado = responderPreguntaConIA($pregunta, $contexto);

        if ($resultado['exito']) {
            $respuesta = $resultado['respuesta'];
            $documentosUsados = array_column($documentosRelevantes, 'nombre_original');
        } else {
            $error = 'No se pudo obtener respuesta de la IA: ' . $resultado['error'];
        }

        // Se guarda en el historial de consultas (mismo criterio que buscar.php)
        $stmt = $pdo->prepare(
            'INSERT INTO consultas_ia (id_usuario, pregunta, respuesta) VALUES (?, ?, ?)'
        );
        $stmt->execute([$idUsuario, $pregunta, $respuesta !== '' ? $respuesta : ('[ERROR] ' . $error)]);
    }
}

// =====================================================
// Historial de preguntas del usuario actual
// (se excluyen los registros que vienen de buscar.php, que empiezan
// con el prefijo "[Búsqueda]")
// =====================================================
$stmt = $pdo->prepare(
    "SELECT pregunta, respuesta, fecha
     FROM consultas_ia
     WHERE id_usuario = ? AND pregunta NOT LIKE '[Búsqueda]%'
     ORDER BY fecha DESC
     LIMIT 10"
);
$stmt->execute([$idUsuario]);
$historial = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Preguntar a la IA - Bufete IA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .respuesta-ia {
            background-color: #eef4ff;
            border-left: 4px solid #0d6efd;
            border-radius: 0.5rem;
            padding: 1rem 1.25rem;
            white-space: pre-line;
        }
        .historial-item { border-left: 3px solid #dee2e6; padding-left: 0.75rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand fw-semibold" href="index.php">
            <i class="bi bi-robot me-1"></i> Bufete Restrepo &amp; Asociados
        </a>
        <div class="d-flex align-items-center">
            <span class="text-light me-3">
                <?= htmlspecialchars($_SESSION['nombre']) ?>
                <span class="badge bg-secondary ms-1"><?= htmlspecialchars(ucfirst($_SESSION['rol'])) ?></span>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right"></i> Salir
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 mt-4">

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-house"></i> Dashboard</a>
        <a href="repositorios.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-folder2-open"></i> Repositorios</a>
        <a href="documentos.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-text"></i> Documentos</a>
        <a href="buscar.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-search"></i> Búsqueda</a>
    </div>

    <h4 class="mb-3"><i class="bi bi-chat-dots me-1"></i> Preguntar a la IA</h4>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="POST" action="consulta_ia.php">
                <label class="form-label small text-muted mb-1">
                    Haz una pregunta sobre el contenido de tus documentos procesados
                </label>
                <div class="input-group">
                    <input type="text" name="pregunta" class="form-control"
                           placeholder="Ej: ¿Cuál es la vigencia del contrato con la empresa XYZ?"
                           value="<?= htmlspecialchars($pregunta) ?>" required autofocus>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Preguntar
                    </button>
                </div>
                <div class="form-text">
                    La IA responde solo con base en los documentos que ya han sido procesados
                    (estado "Procesado"). Si un documento sigue "Pendiente", procésalo primero
                    desde la sección de Documentos.
                </div>
            </form>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($respuesta !== ''): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h6 class="text-muted mb-2"><i class="bi bi-question-circle me-1"></i> Tu pregunta</h6>
                <p class="mb-3"><?= htmlspecialchars($pregunta) ?></p>

                <h6 class="text-muted mb-2"><i class="bi bi-robot me-1"></i> Respuesta de la IA</h6>
                <div class="respuesta-ia mb-3"><?= htmlspecialchars($respuesta) ?></div>

                <?php if (!empty($documentosUsados)): ?>
                    <h6 class="text-muted mb-2 small">Documentos consultados</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($documentosUsados as $nombre): ?>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-file-earmark-text me-1"></i><?= htmlspecialchars($nombre) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- ================= Historial de preguntas ================= -->
    <?php if (!empty($historial)): ?>
        <h5 class="mb-3"><i class="bi bi-clock-history me-1"></i> Historial de preguntas</h5>
        <div class="card shadow-sm">
            <div class="card-body">
                <?php foreach ($historial as $item): ?>
                    <div class="historial-item mb-3">
                        <div class="fw-semibold small">
                            <i class="bi bi-question-circle me-1"></i><?= htmlspecialchars($item['pregunta']) ?>
                        </div>
                        <div class="text-secondary small">
                            <?= htmlspecialchars(mb_substr($item['respuesta'], 0, 220)) ?><?= mb_strlen($item['respuesta']) > 220 ? '…' : '' ?>
                        </div>
                        <div class="text-muted" style="font-size: 0.75rem;">
                            <?= htmlspecialchars(date('d/m/Y H:i', strtotime($item['fecha']))) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>