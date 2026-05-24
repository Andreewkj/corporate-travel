Agents - Instruções para o agente
=================================

Objetivo
--------
Arquivo de referência com regras que o agente (Copilot) deve seguir ao criar ou modificar código neste projeto.

Regras obrigatórias
-------------------
- Nomes de variáveis e parâmetros: devem ser descritivos e ter no mínimo 4 caracteres. Evitar abreviações obscuras ou nomes com 1-3 caracteres (ex.: use `requesterName`, não `r`).
- Todo código PHP gerado deve seguir PSR-12 (formatacao, espaçamento e estilo).
- Todo arquivo PHP novo ou modificado pelo agente deve começar com:

  <?php
  declare(strict_types=1);

  (o `declare(strict_types=1);` é obrigatório em todos os arquivos PHP gerados)
- Antes de finalizar uma alteração que adicionou/alterou arquivos PHP, o agente deve verificar e remover imports não utilizados nos arquivos criados/alterados.

- Comentários: não adicionar comentários que não sejam indispensáveis ao funcionamento do código; evite comentários redundantes ou explicativos que não agreguem valor técnico.

Verificações recomendadas (passos que o agente deve executar)
-----------------------------------------------------------
- Rodar `php -l` nos arquivos modificados para checar sintaxe.
- Rodar um verificador de estilo/PSR-12 (`phpcs --standard=PSR12` ou `php-cs-fixer`) e corrigir problemas de formatação.
- Detectar e remover `use` não utilizados (ex.: via `phpstan`, `php-cs-fixer --rules=no_unused_imports` ou análise estática). Se não for possível executar ferramentas, ao menos inspecionar os `use` e remover os desnecessários.

Boas práticas adicionais
-----------------------
- Funções/métodos: nomes descritivos e verbos no infinitivo para ações (ex.: `cancelRequest`, `updateStatus`).
- DTOs/VOs/Entities/Repositories: seguir convenções de DDD já presentes no repositório.
- Erros/Exceções: usar classes de exceção de domínio (`TravelRequestException`) para regras de negócio.

Como reportar mudanças
----------------------
- Para cada alteração de arquivo, listar os arquivos modificados e um resumo curto do porquê da mudança.

Esta página é normativa: siga-a sempre que o agente fizer alterações no código PHP do projeto.
