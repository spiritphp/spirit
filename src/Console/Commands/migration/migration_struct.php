<?php
$struct = <<<'STRUCT'
<?php
namespace migrations;

use Spirit\DB\Schema;
use Spirit\DB\Schema\Table;
use Spirit\Structure\Migration;

class {{CLASS_NAME}} extends Migration {

	public function up()
	{
		Schema::{{TYPE}}('{{TABLE_NAME}}',function(Table $table){
			{{ACTION_UP}}
		});
	}

	public function down()
	{
		Schema::table('{{TABLE_NAME}}',function(Table $table){
			{{ACTION_DOWN}}
		});
	}

}
STRUCT;

return $struct;