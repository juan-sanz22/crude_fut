<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gerenciamento de Futebol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 10px 10px;
        }
        .card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        .card-title {
            color: #0d6efd;
            font-weight: 600;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1.5rem;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #0a58ca 0%, #084298 100%);
        }
        .icon-container {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(13, 110, 253, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .icon-container i {
            font-size: 1.8rem;
            color: #0d6efd;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 1.5rem 0;
            margin-top: 2rem;
            text-align: center;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
        }
    </style>
</head>
<body>
    <div class="header text-center">
        <div class="container">
            <h1 class="display-4"><i class="fas fa-futbol me-2"></i>Sistema de Gerenciamento de Futebol</h1>
            <p class="lead">Gerencie times, jogadores e partidas de forma eficiente</p>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="icon-container mx-auto">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="card-title">Times</h3>
                        <p class="card-text">Gerencie os times do campeonato, adicione novos times, edite informações ou remova times.</p>
                        <a href="times.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="icon-container mx-auto">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3 class="card-title">Jogadores</h3>
                        <p class="card-text">Gerencie os jogadores, suas posições, números de camisa e times associados.</p>
                        <a href="jogadores.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="icon-container mx-auto">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h3 class="card-title">Partidas</h3>
                        <p class="card-text">Gerencie as partidas, resultados e datas dos jogos entre os times.</p>
                        <a href="partidas.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="container">
            <p>&copy; 2023 Sistema de Gerenciamento de Futebol. Todos os direitos reservados.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>