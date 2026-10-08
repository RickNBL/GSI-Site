<?php
// COPIAR para a pasta ACIMA de public_html como gsi-mail-config.php.
// Substitua os exemplos pelos dados que aparecem em "Conectar dispositivos" do cPanel.
return [
    'smtp_host' => 'SERVIDOR_DE_SAIDA_DO_CPANEL',
    'smtp_port' => 465,
    'smtp_user' => 'CONTA_DE_EMAIL_COMPLETA',
    'smtp_password' => 'SENHA_DA_CONTA_DE_EMAIL',
    'destinatario' => 'EMAIL_QUE_DEVE_RECEBER_AS_MENSAGENS',
];
