Just a test of writing lsp with openswoole tcp.

Adding linebreaks into abstract syntax tree was mistake. It's not exactly a node. Analyzing such things would be more appropiate on lexing.
Reason is, making nodes only visible constructs, it makes range operations much more easy. Don't make not visible things node.

Diagnostics should be seperated within lexer and nodes. Balancing is way to go. Check grammars on lexical analyze, if lexical analyze goes well then analyze the node itself.
