<?php
include 'db.php';

// Buscar times para o dropdown
$stmt = $pdo->query("SELECT id, nome FROM times ORDER BY nome");
$times = $stmt->fetchAll();

// Operações CRUD
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $nome = $_POST['nome'];
        $posicao = $_POST['posicao'];
        $numero_camisa = $_POST['numero_camisa'];
        $time_id = $_POST['time_id'] ?: null;
        
        // Validação da posição
        $posicoes_validas = ['GOL', 'ZAG', 'LD', 'LE', 'VOL', 'MEI', 'ATA'];
        if (!in_array($posicao, $posicoes_validas)) {
            $error = "Posição inválida. Use: GOL, ZAG, LD, LE, VOL, MEI ou ATA.";
        }
        
        // Validação do número da camisa
        elseif ($numero_camisa < 1 || $numero_camisa > 99) {
            $error = "Número da camisa deve estar entre 1 e 99.";
        }
        
        else {
            $stmt = $pdo->prepare("INSERT INTO jogadores (nome, posicao, numero_camisa, time_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $posicao, $numero_camisa, $time_id]);
            header("Location: jogadores.php");
            exit();
        }
    } elseif (isset($_POST['update'])) {
        $id = $_POST['id'];
        $nome = $_POST['nome'];
        $posicao = $_POST['posicao'];
        $numero_camisa = $_POST['numero_camisa'];
        $time_id = $_POST['time_id'] ?: null;
        
        // Validações
        $posicoes_validas = ['GOL', 'ZAG', 'LD', 'LE', 'VOL', 'MEI', 'ATA'];
        if (!in_array($posicao, $posicoes_validas)) {
            $error = "Posição inválida. Use: GOL, ZAG, LD, LE, VOL, MEI ou ATA.";
        }
        
        elseif ($numero_camisa < 1 || $numero_camisa > 99) {
            $error = "Número da camisa deve estar entre 1 e 99.";
        }
        
        else {
            $stmt = $pdo->prepare("UPDATE jogadores SET nome = ?, posicao = ?, numero_camisa = ?, time_id = ? WHERE id = ?");
            $stmt->execute([$nome, $posicao, $numero_camisa, $time_id, $id]);
            header("Location: jogadores.php");
            exit();
        }
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        
        $stmt = $pdo->prepare("DELETE FROM jogadores WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: jogadores.php");
        exit();
    }
}

// Buscar jogadores
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Construir consulta com LEFT JOIN
$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE j.nome LIKE :search1 OR j.posicao LIKE :search2 OR t.nome LIKE :search3";
$params = [
    ':search1' => "%$search%",
    ':search2' => "%$search%",
    ':search3' => "%$search%"
];
}
// Contar total de registros
$count_sql = "SELECT COUNT(*) FROM jogadores j LEFT JOIN times t ON j.time_id = t.id $where";
$stmt = $pdo->prepare($count_sql);

if (!empty($params)) {
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
}

$stmt->execute();
$total = $stmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Buscar dados
$sql = "SELECT j.*, t.nome as time_nome FROM jogadores j LEFT JOIN times t ON j.time_id = t.id $where ORDER BY j.nome 
        ";

$stmt = $pdo->prepare($sql);

if (!empty($params)) {
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
}



$stmt->execute();
$jogadores = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Jogadores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            background: linear-gradient(135deg, #198754 0%, #0f5132 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-success {
            background: linear-gradient(135deg, #198754 0%, #0f5132 100%);
            border: none;
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #0f5132 0%, #0a3622 100%);
        }
        .table th {
            background-color: #198754;
            color: white;
        }
        .pagination .page-item.active .page-link {
            background-color: #198754;
            border-color: #198754;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1 class="text-center"><i class="fas fa-user me-2"></i>Gerenciamento de Jogadores</h1>
        </div>
    </div>

    <div class="container">
        <div class="row mb-4">
            <div class="col-md-6">
                <a href="index.php" class="btn btn-outline-success"><i class="fas fa-arrow-left me-1"></i>Voltar</a>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addJogadorModal">
                    <i class="fas fa-plus me-1"></i>Adicionar Jogador
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
                <h5 class="card-title"><i class="fas fa-search me-2"></i>Buscar Jogadores</h5>
                <form method="GET" action="">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Digite o nome, posição ou time..." name="search" value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-success" type="submit">Buscar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-list me-2"></i>Lista de Jogadores</h5>
                
                <?php if (count($jogadores) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Posição</th>
                                <th>Nº Camisa</th>
                                <th>Time</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jogadores as $jogador): ?>
                            <tr>
                                <td><?= $jogador['id'] ?></td>
                                <td><?= htmlspecialchars($jogador['nome']) ?></td>
                                <td><?= htmlspecialchars($jogador['posicao']) ?></td>
                                <td><?= $jogador['numero_camisa'] ?></td>
                                <td><?= htmlspecialchars($jogador['time_nome'] ?? 'Sem time') ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editJogadorModal" 
                                        data-id="<?= $jogador['id'] ?>" 
                                        data-nome="<?= htmlspecialchars($jogador['nome']) ?>" 
                                        data-posicao="<?= htmlspecialchars($jogador['posicao']) ?>"
                                        data-numero_camisa="<?= $jogador['numero_camisa'] ?>"
                                        data-time_id="<?= $jogador['time_id'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteJogadorModal" 
                                        data-id="<?= $jogador['id'] ?>" 
                                        data-nome="<?= htmlspecialchars($jogador['nome']) ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <?php if ($totalPages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>Nenhum jogador encontrado.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Jogador -->
    <div class="modal fade" id="addJogadorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Novo Jogador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome do Jogador</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="posicao" class="form-label">Posição</label>
                            <select class="form-select" id="posicao" name="posicao" required>
                                <option value="">Selecione a posição</option>
                                <option value="GOL">Goleiro (GOL)</option>
                                <option value="ZAG">Zagueiro (ZAG)</option>
                                <option value="LD">Lateral Direito (LD)</option>
                                <option value="LE">Lateral Esquerdo (LE)</option>
                                <option value="VOL">Volante (VOL)</option>
                                <option value="MEI">Meia (MEI)</option>
                                <option value="ATA">Atacante (ATA)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="numero_camisa" class="form-label">Número da Camisa</label>
                            <input type="number" class="form-control" id="numero_camisa" name="numero_camisa" min="1" max="99" required>
                        </div>
                        <div class="mb-3">
                            <label for="time_id" class="form-label">Time</label>
                            <select class="form-select" id="time_id" name="time_id">
                                <option value="">Selecione o time</option>
                                <?php foreach ($times as $time): ?>
                                <option value="<?= $time['id'] ?>"><?= htmlspecialchars($time['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="create" class="btn btn-success">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Jogador -->
    <div class="modal fade" id="editJogadorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Jogador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_nome" class="form-label">Nome do Jogador</label>
                            <input type="text" class="form-control" id="edit_nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_posicao" class="form-label">Posição</label>
                            <select class="form-select" id="edit_posicao" name="posicao" required>
                                <option value="GOL">Goleiro (GOL)</option>
                                <option value="ZAG">Zagueiro (ZAG)</option>
                                <option value="LD">Lateral Direito (LD)</option>
                                <option value="LE">Lateral Esquerdo (LE)</option>
                                <option value="VOL">Volante (VOL)</option>
                                <option value="MEI">Meia (MEI)</option>
                                <option value="ATA">Atacante (ATA)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_numero_camisa" class="form-label">Número da Camisa</label>
                            <input type="number" class="form-control" id="edit_numero_camisa" name="numero_camisa" min="1" max="99" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_time_id" class="form-label">Time</label>
                            <select class="form-select" id="edit_time_id" name="time_id">
                                <option value="">Selecione o time</option>
                                <?php foreach ($times as $time): ?>
                                <option value="<?= $time['id'] ?>"><?= htmlspecialchars($time['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="update" class="btn btn-success">Atualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Excluir Jogador -->
    <div class="modal fade" id="deleteJogadorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Excluir Jogador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="id" id="delete_id">
                    <div class="modal-body">
                        <p>Tem certeza que deseja excluir o jogador <strong id="delete_nome"></strong>?</p>
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
        var editJogadorModal = document.getElementById('editJogadorModal');
        editJogadorModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var nome = button.getAttribute('data-nome');
            var posicao = button.getAttribute('data-posicao');
            var numero_camisa = button.getAttribute('data-numero_camisa');
            var time_id = button.getAttribute('data-time_id');
            
            var modal = this;
            modal.querySelector('#edit_id').value = id;
            modal.querySelector('#edit_nome').value = nome;
            modal.querySelector('#edit_posicao').value = posicao;
            modal.querySelector('#edit_numero_camisa').value = numero_camisa;
            modal.querySelector('#edit_time_id').value = time_id;
        });

        // Preencher modal de exclusão
        var deleteJogadorModal = document.getElementById('deleteJogadorModal');
        deleteJogadorModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var nome = button.getAttribute('data-nome');
            
            var modal = this;
            modal.querySelector('#delete_id').value = id;
            modal.querySelector('#delete_nome').textContent = nome;
        });
    </script>
</body>
</html>