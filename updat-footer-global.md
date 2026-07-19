# Footer Global Update Guide

## Objetivo
Registrar o processo completo de atualização do rodapé global (Shop + Admin) para que futuras alterações sejam fáceis e consistentes.

## Escopo
Este documento cobre:
- footer do Shop
- footer das páginas de autenticação do Shop
- footer do Admin (dashboard/layout global)
- traduções usadas para suportar o texto dinâmico e o link clicável

## Arquivos alterados

### Shop
1. `packages/Webkul/Shop/src/Resources/lang/*/app.php`
   - Atualiza a chave de tradução do rodapé para o texto:
     `© :current_year BERN OKULEKA LDA. Todos os direitos reservados. Criado e desenvolvido pela :company_link.`
   - O placeholder `:company_link` pode ser usado para inserir o link clicável `AVENATEC LDA`.

2. `packages/Webkul/Shop/src/Resources/views/customers/sign-in.blade.php`
   - Passa `company_link` para a tradução do footer.

3. `packages/Webkul/Shop/src/Resources/views/customers/forgot-password.blade.php`
   - Passa `company_link` para a tradução do footer.

4. `packages/Webkul/Shop/src/Resources/views/customers/sign-up.blade.php`
   - Passa `company_link` para a tradução do footer.

5. `packages/Webkul/Shop/src/Resources/views/customers/reset-password.blade.php`
   - Passa `company_link` para a tradução do footer.

### Admin
1. `packages/Webkul/Admin/src/Resources/views/components/layouts/index.blade.php`
   - Atualiza o layout global do Admin para usar a tradução de footer.
   - Adiciona `current_year` e `company_link` como parâmetros ao `@lang`.

2. `packages/Webkul/Admin/src/Resources/lang/*/app.php`
   - Atualiza a chave de tradução `admin::app.components.layouts.powered-by.description` para:
     `© :current_year BERN OKULEKA LDA. Todos os direitos reservados. Criado e desenvolvido pela :company_link.`
   - Substitui o texto anterior que apontava para Bagisto/Webkul.

## Como foi feito

### 1. Atualizar as traduções
- Localize todas as traduções relevantes em `packages/Webkul/Shop/src/Resources/lang/*/app.php` e `packages/Webkul/Admin/src/Resources/lang/*/app.php`.
- Substitua o texto do footer pelas mensagens com `:current_year` e `:company_link`.
- Mantenha as chaves de tradução consistentes em todos os idiomas.

### 2. Atualizar as views para usar o placeholder `company_link`
- Para o Shop, as views de login e cadastro chamam `@lang('shop::app.customers.*.footer', [...])`.
- Passe o HTML do link:
  ```php
  'company_link' => '<a class="text-blue-600 hover:text-blue-800 underline" href="https://avenatec.it.com" target="_blank" rel="noopener noreferrer">AVENATEC LDA</a>'
  ```
- Isso garante que somente o texto `AVENATEC LDA` seja clicável.

### 3. Atualizar o layout global do Admin
- No arquivo `packages/Webkul/Admin/src/Resources/views/components/layouts/index.blade.php`, substitua o `@lang` existente do rodapé por uma versão que passe `current_year` e `company_link`.
- Exemplo:
  ```php
  @lang('admin::app.components.layouts.powered-by.description', [
      'company_link' => '<a class="text-blue-600 hover:underline dark:text-darkBlue" href="https://avenatec.it.com" target="_blank" rel="noopener noreferrer">AVENATEC LDA</a>',
      'current_year' => date('Y'),
  ])
  ```

### 4. Commit e push
- Após verificar as alterações, use os comandos Git:
  ```bash
  git add <arquivos modificados>
  git commit -m "Update footer text and links for Shop and Admin"
  git push
  ```

## Observações importantes
- Use `date('Y')` para manter o ano atual dinâmico.
- O link deve abrir em nova aba com `target="_blank"` e `rel="noopener noreferrer"`.
- Sempre revise as traduções em todos os idiomas se a chave for alterada.
- Evite colocar o link completo dentro do texto fixo; use o placeholder `:company_link`.

## Onde verificar
- Shop: abra páginas de login, registro, esqueci senha e reset de senha.
- Admin: abra `/admin/dashboard` e confirme o rodapé no layout global.

## Exemplo de resultado final
- Texto fixo: `© 2026 BERN OKULEKA LDA. Todos os direitos reservados. Criado e desenvolvido pela `
- Link clicável: `AVENATEC LDA`
- URL: `https://avenatec.it.com`
- Comportamento: abre em nova aba.
