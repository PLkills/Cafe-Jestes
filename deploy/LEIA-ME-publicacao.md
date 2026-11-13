# Como publicar o Cafe Jester na Kinghost

## Opcao A (recomendada, mais segura): sistema direto em cafejester.com.br
Estrutura final no servidor:

    /home/SEU_USUARIO/            <- uma pasta ACIMA da raiz do site (nao publica)
        app/   config/   sql/     <- coloque aqui (fora da web)
        www/                      <- raiz do site (public_html ou www)
            index.php  css/  js/  img/  uploads/     <- CONTEUDO de public/

1. Renomeie/mova a pagina "Site em breve" (index.html) para `em-breve.html`, senao ela abre antes do index.php.
2. Envie o CONTEUDO da pasta `public/` para a raiz do site.
3. Envie `app/`, `config/` e `sql/` para a pasta acima da raiz. O index.php ja procura `../app` e `../config`.
4. Mantenha o seu `config/database.php` e de permissao de escrita a `uploads/`.
5. Se o seu FTP so permite gravar DENTRO da raiz do site, use a Opcao B.

## Opcao B: projeto inteiro dentro da raiz do site
1. Envie tudo para a raiz (pastas app, config, public, sql).
2. Salve `deploy/htaccess_raiz_opcao_B.txt` como `.htaccess` na raiz.
3. Renomeie o index.html da pagina "em breve" para `em-breve.html`.
4. Teste: `/` abre a vitrine; `/sql/schema.sql` e `/config/database.php` devem dar 403.

## Depois do SSL
Descomente as 3 linhas "Forca HTTPS" do .htaccess.
