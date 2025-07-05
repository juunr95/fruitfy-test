<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Contato Criado</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .contact-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .contact-info h3 {
            margin-top: 0;
            color: #667eea;
        }
        .info-item {
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .value {
            color: #333;
            margin-left: 10px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✉️ Novo Contato Criado</h1>
        <p>Um novo contato foi adicionado ao sistema</p>
    </div>

    <div class="content">
        <p>Olá,</p>
        
        <p>Um novo contato foi criado no sistema. Aqui estão os detalhes:</p>

        <div class="contact-info">
            <h3>📋 Informações do Contato</h3>
            
            <div class="info-item">
                <span class="label">Nome:</span>
                <span class="value">{{ $contact->name }}</span>
            </div>
            
            <div class="info-item">
                <span class="label">Email:</span>
                <span class="value">{{ $contact->email }}</span>
            </div>
            
            <div class="info-item">
                <span class="label">Telefone:</span>
                <span class="value">{{ $contact->phone }}</span>
            </div>
            
            <div class="info-item">
                <span class="label">Criado em:</span>
                <span class="value">{{ $contact->created_at->format('d/m/Y H:i:s') }}</span>
            </div>
        </div>

        <p>Este contato foi adicionado automaticamente ao sistema e está disponível para gerenciamento.</p>
    </div>

    <div class="footer">
        <p>Sistema de Contatos - Notificação Automática</p>
        <p>Esta mensagem foi enviada automaticamente, não responda a este email.</p>
    </div>
</body>
</html> 