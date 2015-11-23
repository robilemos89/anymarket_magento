Desinstalação do módulo de integração AnyMarket
===============================================

Para desinstalar o módulo devem ser removidos os diretórios:
 - app\code\community\DB1
 - app\etc\modules\DB1_AnyMarket.xml

O módulo estará inativo, mas para uma desinstalação completa remover também:
 - app\design\adminhtml\default\default\layout\db1_anymarket.xml
 - app\design\adminhtml\default\default\template\db1
 - app\locale\pt_BR\DB1_AnyMarket.csv
 - app\locale\en_US\DB1_AnyMarket.csv
 
E executar o script: **DB1_AnyMarket_uninstall.sql** para remoção das tabelas e limpeza de dados.

Mais informações
--------
Caso tenha dúvidas, estamos à disposição para atendê-lo no que for preciso: http://www.anymarket.com.br/ ou em nosso [blog].

[blog]: http://marketplace.anymarket.com.br/

