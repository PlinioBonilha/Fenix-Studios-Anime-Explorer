
<?php
$busca = trim($_GET["q"] ?? "");
$animes = [];
$erro = "";

if ($busca !== "") {
    // Monta o endereço da API com o texto pesquisado
    $url = "https://api.tenrai.org/v1/anime?q="
         . urlencode($busca)
         . "&limit=12";

    // Verifica se o cURL está disponível no PHP
    if (!function_exists("curl_init")) {
        $erro = "Ative a extensão cURL no PHP do XAMPP.";
    } else {
        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => "AnimeExplorer/1.0"
        ]);

        $resposta = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($resposta === false) {
            $erro = "Não foi possível conectar à API.";
        } elseif ($status !== 200) {
            $erro = "A API retornou o código HTTP " . $status . ".";
        } else {
            $dados = json_decode($resposta, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $erro = "A resposta recebida não é um JSON válido.";
            } elseif (!isset($dados["data"]) ||
                      !is_array($dados["data"])) {
                $erro = "A resposta da API veio em um formato inesperado.";
            } else {
                $animes = $dados["data"];
            }
        }

        curl_close($curl);
    }
}

function e($valor) {
    return htmlspecialchars(
        (string) ($valor ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Fenix Studios Anime Explorer</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #170808;
            color: #fff2ed;
        }

        header {
            text-align: center;
            padding: 40px 20px 30px;
            background: linear-gradient(135deg, #260606, #591010, #8b1a0a);
            border-bottom: 2px solid #e34218;
            box-shadow: 0 5px 25px rgba(255, 55, 15, 0.15);
        }

        header h1 {
            color: #ff5722;
            margin-bottom: 10px;
            text-shadow: 0 0 15px rgba(255, 69, 0, 0.5);
        }

        header p {
            color: #ffd5b8;
        }

        form {
            display: flex;
            gap: 10px;
            justify-content: center;
            max-width: 650px;
            margin: 25px auto 0;
        }

        input {
            flex: 1;
            min-width: 0;
            padding: 13px;
            border-radius: 8px;
            border: 1px solid #96331f;
            background: #321313;
            color: #fff2ed;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input:focus {
            border-color: #ff5722;
            box-shadow: 0 0 10px rgba(255, 69, 0, 0.3);
        }

        input::placeholder {
            color: #c49a8b;
        }

        button {
            padding: 13px 18px;
            background: linear-gradient(135deg, #ff5722, #d7190c);
            color: white;
            border: 1px solid #ff784e;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }

        button:hover {
            background: linear-gradient(135deg, #ff7a18, #f02b16);
            box-shadow: 0 0 16px rgba(255, 69, 0, 0.55);
            transform: translateY(-2px);
        }

        main {
            max-width: 1200px;
            margin: auto;
            padding: 25px 20px 50px;
        }

        main h2 {
            color: #ff8a50;
        }

        .grade {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background: linear-gradient(145deg, #291111, #1e0b0b);
            border: 1px solid #63241b;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s, border-color 0.3s,
                        box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            border-color: #ff5722;
            box-shadow: 0 8px 24px rgba(255, 55, 15, 0.2);
        }

        .card img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            background: #321313;
        }

        .conteudo {
            padding: 15px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .conteudo h3 {
            color: #ffe1d1;
        }

        .nota {
            color: #ffc247;
            font-weight: bold;
        }

        .sinopse {
            color: #e0c7bf;
            font-size: 14px;
            line-height: 1.5;
        }

        .link {
            display: inline-block;
            margin-top: auto;
            padding-top: 12px;
            color: #ff8057;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.2s;
        }

        .link:hover {
            color: #ffc247;
            text-decoration: underline;
        }

        .mensagem {
            padding: 20px;
            background: #291111;
            border: 1px solid #63241b;
            border-left: 4px solid #ff5722;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        @media (max-width: 480px) {
            form {
                flex-direction: column;
            }

            .card img {
                height: 260px;
            }
        }
    </style>
</head>

<body>
<header>
    <h1>Fenix Studios Anime Explorer</h1>
    <p>Descubra animes, personagens e novas histórias.</p>

    <form method="GET" action="">
        <input
            type="search"
            name="q"
            placeholder="Ex.: Naruto, One Piece..."
            value="<?= e($busca) ?>"
            required
        >

        <button type="submit">Pesquisar</button>
    </form>
</header>

<main>
    <?php if ($erro !== ""): ?>

        <div class="mensagem">
            <strong>Ops!</strong>
            <p><?= e($erro) ?></p>
        </div>

    <?php elseif ($busca !== "" && count($animes) === 0): ?>

        <div class="mensagem">
            Nenhum anime encontrado para
            "<?= e($busca) ?>". Tente outro título.
        </div>

    <?php elseif (count($animes) > 0): ?>

        <h2>Resultados para "<?= e($busca) ?>"</h2>

        <section class="grade">
            <?php foreach ($animes as $anime): ?>
                <?php
                    $titulo = $anime["title"] ?? "Título indisponível";
                    $imagem = $anime["images"]["jpg"]["large_image_url"]
                        ?? $anime["images"]["jpg"]["image_url"]
                        ?? "";

                    $nota = $anime["score"] ?? null;
                    $sinopse = $anime["synopsis"] ?? "";
                    $link = $anime["url"] ?? "";
                ?>

                <article class="card">
                    <?php if ($imagem !== ""): ?>
                        <img
                            src="<?= e($imagem) ?>"
                            alt="Capa de <?= e($titulo) ?>"
                            loading="lazy"
                        >
                    <?php endif; ?>

                    <div class="conteudo">
                        <h3><?= e($titulo) ?></h3>

                        <p class="nota">
                            Nota:
                            <?= $nota !== null
                                ? e($nota) . " / 10"
                                : "Sem avaliação" ?>
                        </p>

                        <p class="sinopse">
                            <?= e($sinopse !== ""
                                ? mb_strimwidth(
                                    $sinopse, 0, 220, "..."
                                )
                                : "Sinopse não disponível.") ?>
                        </p>

                        <?php if ($link !== ""): ?>
                            <a
                                class="link"
                                href="<?= e($link) ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                Ver mais informações ↗
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

    <?php else: ?>

        <div class="mensagem">
            <h2>Encontre seu próximo anime favorito!</h2>
            <p>
                Digite um título na barra de pesquisa
                para consultar os dados da API.
            </p>
        </div>

    <?php endif; ?>
</main>
</body>
</html>
