<?php
// Configuração do banco de dados
$host = 'localhost';
$db = 'futebol_db';
$user = 'root';
$pass = 'root';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// Buscar times para o dropdown
$stmt = $pdo->query("SELECT id, nome FROM times ORDER BY nome");
$times = $stmt->fetchAll();

// Operações CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $time_casa_id = $_POST['time_casa_id'];
        $time_fora_id = $_POST['time_fora_id'];
        $data_jogo = $_POST['data_jogo'];
        $gols_casa = $_POST['gols_casa'];
        $gols_fora = $_POST['gols_fora'];
        
        // Validação: times não podem ser iguais
        if ($time_casa_id == $time_fora_id) {
            $error = "Os times não podem ser iguais.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO partidas (time_casa_id, time_fora_id, data_jogo, gols_casa, gols_fora) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$time_casa_id, $time_fora_id, $data_jogo, $gols_casa, $gols_fora]);
        }
    } elseif (isset($_POST['update'])) {
        $id = $_POST['id'];
        $time_casa_id = $_POST['time_casa_id'];
        $time_fora_id = $_POST['time_fora_id'];
        $data_jogo = $_POST['data_jogo'];
        $gols_casa = $_POST['gols_casa'];
        $gols_fora = $_POST['gols_fora'];
        
        // Validação: times não podem ser iguais
        if ($time_casa_id == $time_fora_id) {
            $error = "Os times não podem ser iguais.";
        } else {
            $stmt = $pdo->prepare("UPDATE partidas SET time_casa_id = ?, time_fora_id = ?, data_jogo = ?, gols_casa = ?, gols_fora = ? WHERE id = ?");
            $stmt->execute([$time_casa_id, $time_fora_id, $data_jogo, $gols_casa, $gols_fora, $id]);
        }
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        
        $stmt = $pdo->prepare("DELETE FROM partidas WHERE id = ?");
        $stmt->execute([$id]);
    }
}

// Buscar partidas
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$where = '';
if (!empty($search)) {
    $where = "WHERE tc.nome LIKE :search OR tf.nome LIKE :search OR p.data_jogo LIKE :search";
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM partidas p 
                      LEFT JOIN times tc ON p.time_casa_id = tc.id 
                      LEFT JOIN times tf ON p.time_fora_id = tf.id 
                      $where");
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%");
}
$stmt->execute();
$total = $stmt->fetchColumn();
$totalPages = ceil($total / $limit);

$stmt = $pdo->prepare("SELECT p.*, tc.nome as time_casa_nome, tf.nome as time_fora_nome 
                      FROM partidas p 
                      LEFT JOIN times tc ON p.time_casa_id = tc.id 
                      LEFT JOIN times tf ON p.time_fora_id = tf.id 
                      $where ORDER BY p.data_jogo DESC LIMIT :limit OFFSET :offset");
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%");
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$partidas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Partidas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            background: linear-gradient(135deg, #6f42c1 0%, #4a2d80 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #6f42c1 0%, #4a2d80 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #4a2d80 0%, #372063 100%);
        }
        .table th {
            background-color: #6f42c1;
            color: white;
        }
        .pagination .page-item.active .page-link {
            background-color: #6f42c1;
            border-color: #6f42c1;
        }
        .resultado {
            font-weight: bold;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1 class="text-center"><i class="fas fa-calendar-alt me-2"></i>Gerenciamento de Partidas</h1>
        </div>
    </div>

    <div class="container">
        <div class="row mb-4">
            <div class="col-md-6">
                <a href="index.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-1"></i>Voltar</a>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPartidaModal">
                    <i class="fas fa-plus me-1"></i>Adicionar Partida
                </button>
            </div>
        </div>

        <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-search me-2"></i>Buscar Partidas</h5>
                <form method="GET" action="">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Digite o nome do time ou data..." name="search" value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-primary" type="submit">Buscar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-list me-2"></i>Lista de Partidas</h5>
                
                <?php if (count($partidas) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Data</th>
                                <th>Time da Casa</th>
                                <th>Time Visitante</th>
                                <th>Placar</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($partidas as $partida): ?>
                            <tr>
                                <td><?= $partida['id'] ?></td>
                                <td><?= date('d/m/Y', strtotime($partida['data_jogo'])) ?></td>
                                <td><?= htmlspecialchars($partida['time_casa_nome']) ?></td>
                                <td><?= htmlspecialchars($partida['time_fora_nome']) ?></td>
                                <td class="resultado"><?= $partida['gols_casa'] ?> x <?= $partida['gols_fora'] ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editPartidaModal" 
                                        data-id="<?= $partida['id'] ?>" 
                                        data-time_casa_id="<?= $partida['time_casa_id'] ?>"
                                        data-time_fora_id="<?= $partida['time_fora_id'] ?>"
                                        data-data_jogo="<?= $partida['data_jogo'] ?>"
                                        data-gols_casa="<?= $partida['gols_casa'] ?>"
                                        data-gols_fora="<?= $partida['gols_fora'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deletePartidaModal" 
                                        data-id="<?= $partida['id'] ?>" 
                                        data-time_casa="<?= htmlspecialchars($partida['time_casa_nome']) ?>"
                                        data-time_fora="<?= htmlspecialchars($partida['time_fora_nome']) ?>"
                                        data-data_jogo="<?= date('d/m/Y', strtotime($partida['data_jogo'])) ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>Nenhuma partida encontrada.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Partida -->
    <div class="modal fade" id="addPartidaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Nova Partida</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="time_casa_id" class="form-label">Time da Casa</label>
                                <select class="form-select" id="time_casa_id" name="time_casa_id" required>
                                    <option value="">Selecione o time</option>
                                    <?php foreach ($times as $time): ?>
                                    <option value="<?= $time['id'] ?>"><?= htmlspecialchars($time['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="time_fora_id" class="form-label">Time Visitante</label>
                                <select class="form-select" id="time_fora_id" name="time_fora_id" required>
                                    <option value="">Selecione o time</option>
                                    <?php foreach ($times as $time): ?>
                                    <option value="<?= $time['id'] ?>"><?= htmlspecialchars($time['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="data_jogo" class="form-label">Data do Jogo</label>
                            <input type="date" class="form-control" id="data_jogo" name="data_jogo" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="gols_casa" class="form-label">Gols do Time da Casa</label>
                                <input type="number" class="form-control" id="gols_casa" name="gols_casa" min="0" value="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gols_fora" class="form-label">Gols do Time Visitante</label>
                                <input type="number" class="form-control" id="gols_fora" name="gols_fora" min="0" value="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="create" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Partida -->
    <div class="modal fade" id="editPartidaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Partida</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_time_casa_id" class="form-label">Time da Casa</label>
                                <select class="form-select" id="edit_time_casa_id" name="time_casa_id" required>
                                    <option value="">Selecione o time</option>
                                    <?php foreach ($times as $time): ?>
                                    <option value="<?= $time['id'] ?>"><?= htmlspecialchars($time['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_time_fora_id" class="form-label">Time Visitante</label>
                                <select class="form-select" id="edit_time_fora_id" name="time_fora_id" required>
                                    <option value="">Selecione o time</option>
                                    <?php foreach ($times as $time): ?>
                                    <option value="<?= $time['id'] ?>"><?= htmlspecialchars($time['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_data_jogo" class="form-label">Data do Jogo</label>
                            <input type="date" class="form-control" id="edit_data_jogo" name="data_jogo" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_gols_casa" class="form-label">Gols do Time da Casa</label>
                                <input type="number" class="form-control" id="edit_gols_casa" name="gols_casa" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_gols_fora" class="form-label">Gols do Time Visitante</label>
                                <input type="number" class="form-control" id="edit_gols_fora" name="gols_fora" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="update" class="btn btn-primary">Atualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Excluir Partida -->
    <div class="modal fade" id="deletePartidaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Excluir Partida</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="id" id="delete_id">
                    <div class="modal-body">
                        <p>Tem certeza que deseja excluir a partida entre <strong id="delete_time_casa"></strong> e <strong id="delete_time_fora"></strong> do dia <strong id="delete_data_jogo"></strong>?</p>
                        <p class="text-danger">Esta ação não pode ser desfeita.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="delete" class="btn btn-danger">Excluir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preencher modal de edição
        var editPartidaModal = document.getElementById('editPartidaModal');
        editPartidaModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var time_casa_id = button.getAttribute('data-time_casa_id');
            var time_fora_id = button.getAttribute('data-time_fora_id');
            var data_jogo = button.getAttribute('data-data_jogo');
            var gols_casa = button.getAttribute('data-gols_casa');
            var gols_fora = button.getAttribute('data-gols_fora');
            
            var modal = this;
            modal.querySelector('#edit_id').value = id;
            modal.querySelector('#edit_time_casa_id').value = time_casa_id;
            modal.querySelector('#edit_time_fora_id').value = time_fora_id;
            modal.querySelector('#edit_data_jogo').value = data_jogo;
            modal.querySelector('#edit_gols_casa').value = gols_casa;
            modal.querySelector('#edit_gols_fora').value = gols_fora;
        });

        // Preencher modal de exclusão
        var deletePartidaModal = document.getElementById('deletePartidaModal');
        deletePartidaModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var time_casa = button.getAttribute('data-time_casa');
            var time_fora = button.getAttribute('data-time_fora');
            var data_jogo = button.getAttribute('data-data_jogo');
            
            var modal = this;
            modal.querySelector('#delete_id').value = id;
            modal.querySelector('#delete_time_casa').textContent = time_casa;
            modal.querySelector('#delete_time_fora').textContent = time_fora;
            modal.querySelector('#delete_data_jogo').textContent = data_jogo;
        });
    </script>
</body>
</html>