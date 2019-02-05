<?php namespace ewma\schemas;

class Session extends \Schema
{
    public $table = 'ewma_sessions';

    public function blueprint()
    {
        return function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('close_time')->default(0);
            $table->string('module_namespace')->default('');
            $table->string('node_path')->default('');
            $table->string('node_instance')->default('');
            $table->string('key', 32)->default('');
            $table->longText('data');

            $table->index('key');
            $table->index('module_namespace');
            $table->index([
                              \DB::raw('module_namespace(16)'),
                              \DB::raw('node_instance(16)'),
                              \DB::raw('`key`(6)')
                          ], 'ewma_sessions_module_namespace_node_instance_key');
        };
    }
}
