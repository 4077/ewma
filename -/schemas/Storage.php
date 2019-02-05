<?php namespace ewma\schemas;

class Storage extends \Schema
{
    public $table = 'ewma_storage';

    public function blueprint()
    {
        return function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('module_namespace')->default('');
            $table->string('node_path')->default('');
            $table->string('node_instance')->default('');
            $table->longText('data');

            $table->index('module_namespace');
            $table->index([
                              \DB::raw('module_namespace(16)'),
                              \DB::raw('node_instance(16)')
                          ], 'ewma_storage_module_namespace_node_instance');
        };
    }
}
