<?php 
session_start();

require_once __DIR__ . '/../config/Database.php';

// Define o caminho absoluto baseado na estrutura interna do Docker
// Como o arquivo está em /src/public, subimos um nível (..) e entramos em /src/views
$viewsPath = realpath(__DIR__ . '/../views');

if (!$viewsPath || !file_exists($viewsPath . '/includes/head.php')) {
    // Fallback de segurança caso a estrutura de pastas src esteja duplicada
    $viewsPath = realpath(__DIR__ . '/../../src/views');
}

if (!$viewsPath) {
    die("Erro Crítico: Pasta de views não encontrada.");
}

require_once $viewsPath . '/includes/head.php';
require_once $viewsPath . '/includes/header.php';

$message_sent = false;
$error_message = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validação de entrada
        $name = trim($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Validações
        if (!$name || !$email || !$subject || !$message) {
            throw new Exception("Todos os campos são obrigatórios.");
        }

        if (strlen($message) < 10) {
            throw new Exception("A mensagem deve ter no mínimo 10 caracteres.");
        }

        $db = new Database();
        $conn = $db->getConnection();

        if (!$conn) {
            throw new Exception("Erro de conexão com o banco de dados.");
        }
        
        $stmt = $conn->prepare("INSERT INTO contacts (name, email, subject, message) VALUES (:name, :email, :subject, :message)");
        
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $message_sent = true;
        } else {
            throw new Exception("Erro ao enviar mensagem. Tente novamente.");
        }
    } catch (Exception $e) {
        error_log("Erro ao enviar contato: " . $e->getMessage());
        $error_message = $e->getMessage();
    }
}
?>

<main class="contact-page">
    <div class="contact-header">
        <h1>Fale Conosco</h1>
        <p>Estamos aqui para ouvir você. Sinta-se à vontade para tirar dúvidas sobre nossos produtos ou saúde íntima.</p>
    </div>

    <section class="contact-content">
        <div class="contact-info">
            <h3>Canais de Atendimento</h3>
            <p><strong>E-mail:</strong> atendimento@essence.com.br</p>
            <p><strong>WhatsApp:</strong> +55 (11) 99999-9999</p>
            <p><strong>Horário:</strong> Seg a Sex, das 09h às 18h</p>
        </div>

        <div class="contact-form-wrapper">
            <?php if ($message_sent): ?>
                <div class="success-message">
                    <p>Sua mensagem foi enviada com sucesso. Em breve entraremos em contato.</p>
                </div>
            <?php else: ?>
                <form action="contato.php" method="POST" class="essence-form">
                    <div class="form-group">
                        <label for="name">Nome Completo</label>
                        <input type="text" id="name" name="name" required placeholder="Como gostaria de ser chamada?">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" required placeholder="seu@email.com">
                    </div>

                    <div class="form-group">
                        <label for="subject">Assunto</label>
                        <select id="subject" name="subject">
                            <option value="Dúvida sobre produto">Dúvida sobre produto</option>
                            <option value="Trocas e Devoluções">Trocas e Devoluções</option>
                            <option value="Sugestões">Sugestões</option>
                            <option value="Saúde Íntima">Saúde Íntima</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">Mensagem</label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                    </div>

                    <button type="submit" class="btn-submit">Enviar Mensagem</button>
                </form>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include $viewsPath . '/includes/footer.php'; ?>