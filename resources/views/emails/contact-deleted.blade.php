<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato Deletado</title>
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
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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
            border-left: 4px solid #ef4444;
        }
        .contact-info h3 {
            margin-top: 0;
            color: #ef4444;
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
        .warning {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
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
        <h1>🗑️ Contato Deletado</h1>
        <p>Um contato foi removido do sistema</p>
    </div>

    <div class="content">
        <p>Olá,</p>
        
        <p>Um contato foi deletado do sistema. Aqui estão os detalhes do contato removido:</p>

        <div class="contact-info">
            <h3>📋 Informações do Contato Deletado</h3>
            
            <div class="info-item">
                <span class="label">Nome:</span>
                <span class="value">{{ $contactData['name'] }}</span>
            </div>
            
            <div class="info-item">
                <span class="label">Email:</span>
                <span class="value">{{ $contactData['email'] }}</span>
            </div>
            
            <div class="info-item">
                <span class="label">Telefone:</span>
                <span class="value">{{ $contactData['phone'] }}</span>
            </div>
            
            <div class="info-item">
                <span class="label">Deletado em:</span>
                <span class="value">{{ now()->format('d/m/Y H:i:s') }}</span>
            </div>
        </div>

        <div class="warning">
            <strong>⚠️ Atenção:</strong> Esta ação não pode ser desfeita. O contato foi permanentemente removido do sistema.
        </div>

        <p>Se esta ação foi realizada por engano, será necessário recriar o contato manualmente.</p>
    </div>

    <div class="footer">
        <p>Sistema de Contatos - Notificação Automática</p>
        <p>Esta mensagem foi enviada automaticamente, não responda a este email.</p>
    </div>
</body>
</html> 