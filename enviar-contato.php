<?php
/**
 * GSI Tecnologia — endpoint do formulário de contato.
 * Requer PHPMailer em /public_html/phpmailer/src e configuração fora de public_html.
 */

declare(strict_types=1);

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

function voltar(string $status): void
{
    header('Location: /contato/?envio=' . $status, true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit;
}

// Campo invisível para visitantes; robôs costumam preenchê-lo.
if (trim((string)($_POST['website'] ?? '')) !== '') {
    voltar('ok');
}

$nome = trim((string)($_POST['name'] ?? ''));
$empresa = trim((string)($_POST['company'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$telefone = trim((string)($_POST['phone'] ?? ''));
$servico = trim((string)($_POST['service'] ?? ''));
$mensagem = trim((string)($_POST['message'] ?? ''));

if ($nome === '' || $email === '' || $telefone === '' || $servico === '' || $mensagem === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($nome) > 500 || strlen($empresa) > 650 || strlen($email) > 254 ||
    strlen($telefone) > 100 || strlen($servico) > 300 || strlen($mensagem) > 15000) {
    voltar('erro');
}

// O config deve ficar fora da pasta pública da hospedagem.
$configPath = dirname(__DIR__) . '/gsi-mail-config.php';
$libraryDir = __DIR__ . '/phpmailer/src';

try {
    if (!is_file($configPath) ||
        !is_file($libraryDir . '/PHPMailer.php') ||
        !is_file($libraryDir . '/SMTP.php') ||
        !is_file($libraryDir . '/Exception.php')) {
        throw new RuntimeException('Configuração de e-mail ou PHPMailer não encontrado.');
    }

    $config = require $configPath;
    if (!is_array($config) || empty($config['smtp_host']) || empty($config['smtp_user']) ||
        empty($config['smtp_password']) || empty($config['destinatario'])) {
        throw new RuntimeException('Configuração SMTP incompleta.');
    }

    require_once $libraryDir . '/Exception.php';
    require_once $libraryDir . '/PHPMailer.php';
    require_once $libraryDir . '/SMTP.php';

    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host = (string)$config['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = (string)$config['smtp_user'];
    $mail->Password = (string)$config['smtp_password'];
    $mail->Port = (int)($config['smtp_port'] ?? 465);
    $mail->SMTPSecure = $mail->Port === 587
        ? PHPMailer::ENCRYPTION_STARTTLS
        : PHPMailer::ENCRYPTION_SMTPS;
    $mail->Timeout = 15;
    $mail->setFrom((string)$config['smtp_user'], 'GSI Tecnologia');
    $mail->addAddress((string)$config['destinatario']);
    $mail->addReplyTo($email, $nome);
    $mail->isHTML(false);
    $mail->Subject = 'Solicitação de proposta — GSI Tecnologia';
    $mail->Body = "Nome: {$nome}\nEmpresa: {$empresa}\nE-mail: {$email}\nTelefone/WhatsApp: {$telefone}\nServiço de interesse: {$servico}\n\nConte-nos sobre sua necessidade:\n{$mensagem}\n";
    $mail->send();
    voltar('ok');
} catch (Throwable $e) {
    error_log('Falha no formulario GSI: ' . $e->getMessage());
    voltar('erro');
}
