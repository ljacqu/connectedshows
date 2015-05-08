<?php $config = array (
  'db_name' => 'showconnections',
  'db_user' => 'root',
  'db_pass' => '',
  'graph_command' => 'dot',
  'commands' => 
  array (
    'svg' => 'dot -Tsvg {file} -o {output}',
    'png' => 'dot -Tpng {file} -o {output}',
  ),
);