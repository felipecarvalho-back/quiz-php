<?php
session_start();

$quizFile = __DIR__ . '/quiz.json';
$perguntas = [];
$gabarito = [];

if (file_exists($quizFile)) {
    $conteudo = file_get_contents($quizFile);
    $dados = json_decode($conteudo, true) ?? [];

    $gabarito = $dados['respostas'] ?? [];

    unset($dados['respostas']);
    $perguntas = $dados;
}

if (!isset($_SESSION['respostas'])) {
    $_SESSION['respostas'] = [];
}

$questoes = isset($_GET['q']) && is_numeric($_GET['q']) ? (int)$_GET['q'] : 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $questaoAtual = (int)$_POST['questao_atual'];

    if (isset($_POST['resposta'])) {
        $_SESSION['respostas'][$questaoAtual] = $_POST['resposta'];
    }

    if ($questaoAtual < count($perguntas)) {
        $proxima = $questaoAtual + 1;
        header("Location: index.php?q=" . $proxima);
        exit;
    } else {
        header("Location: index.php?fim=true");
        exit;
    }
}

if (isset($_GET['fim'])) {
    $respostas_forms = array_values($_SESSION['respostas'] ?? []);
    $gabarito_alinhado = array_values($gabarito);

    $acertos = count(array_intersect_assoc($respostas_forms, $gabarito_alinhado));
    $total = count($perguntas);

    echo <<<HTML
        <h2>Acertou $acertos/$total</h2>
    HTML;

    echo '<br><a href="index.php?reset=true">Reiniciar</a>';
    exit;
}

if (isset($_GET['reset'])) {
    unset($_SESSION['respostas']);
    header("Location: index.php");
    exit;
}
?>

<form action="" method="post">
    <input type="hidden" name="questao_atual" value="<?= $questoes ?>">
    <h2>Questão <?= $questoes ?> - <?= count($perguntas) ?></h2>
    <h3>Enunciado: <?= $perguntas[$questoes]['questao'] ?></h3>
    <?php foreach ($perguntas[$questoes]['alternativa'] as $value => $letra) { ?>
        <input type="radio" name="resposta" value="<?= $value ?>" id="opt_<?= $value ?>" required>
        <label for="opt_<?= $value ?>"><?= $value ?>) <?= $letra ?></label>
    <?php } ?>

    <?php if ($questoes < count($perguntas)) { ?>
        <button type="submit">Proxima</button>
    <?php } else { ?>
        <button type="submit">Enviar</button>
    <?php } ?>
</form>