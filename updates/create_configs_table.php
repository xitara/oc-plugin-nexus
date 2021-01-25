<?php namespace Xitara\Nexus\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateConfigsTable extends Migration
{
    public function up()
    {
        Schema::create('xitara_nexus_configs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('xitara_nexus_configs');
    }
}
