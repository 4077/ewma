<?php namespace ewma\Storage;

use ewma\App\App;
use ewma\Service\Service;
use ewma\models\Storage as StorageModel;

class Storage extends Service
{
    protected $services = ['app'];

    /**
     * @var App
     */
    public $app = App::class;

    protected function boot()
    {
        $this->app->events->bind('app/terminate', function () {
            $this->save();
        });
    }

    /**
     * Загруженные узлы
     *
     * [[moduleNamespace][nodeInstance][nodePath] => data]
     *
     */
    public $nodes = [];

    /**
     * Копия узлов на момент загрузки
     */
    private $nodesOriginal;

    /**
     * Созданные узлы
     */
    private $newNodes;

    /**
     * Запрет сохранения
     */
    private $readonly;

    public function setReadonly()
    {
        $this->readonly = true;
    }

    /**
     * Получение узла или ссылки на него
     *
     * @param $modulePath
     * @param $nodePath
     *
     * @return mixed
     */
    public function &getNode($modulePath, $nodePath, $nodeInstance = '')
    {
        $module = $this->app->modules->getByPath($modulePath);

        if ($module) {
            $moduleNamespace = $module->namespace;

            if (!isset($this->nodes[$moduleNamespace][$nodeInstance])) {
                $this->nodes[$moduleNamespace][$nodeInstance] = StorageModel
                    ::where('module_namespace', $moduleNamespace)
                    ->where('node_instance', $nodeInstance)
                    ->pluck('data', 'node_path')->all();

                $this->nodesOriginal[$moduleNamespace][$nodeInstance] = $this->nodes[$moduleNamespace][$nodeInstance];
            }

            if (!isset($this->nodes[$moduleNamespace][$nodeInstance][$nodePath])) {
                $this->nodes[$moduleNamespace][$nodeInstance][$nodePath] = [];
                $this->newNodes[$moduleNamespace][$nodeInstance][$nodePath] = true;
            }

            $node = &$this->nodes[$moduleNamespace][$nodeInstance][$nodePath];

            return $node;
        }

        $null = null;
        $node = &$null;

        return $node;
    }

    /**
     * Сохранение всех узлов, или только всех узлов модуля, или только определенного узла
     *
     * @param bool|string $moduleNamespace
     * @param bool|string $nodePath
     * @param null|string $nodeInstance
     */
    public function save($moduleNamespace = false, $nodePath = false, $nodeInstance = '')
    {
        if (!$this->readonly) {
            if ($moduleNamespace) {
                if ($nodePath) {
                    $this->nodeSave($moduleNamespace, $nodePath, $nodeInstance);
                } else {
                    $this->moduleNodesSave($moduleNamespace);
                }
            } else {
                $this->saveAll();
            }
        }
    }

    /**
     * Сохранение всех загруженных или созданных узлов
     */
    private function saveAll()
    {
        foreach (array_keys($this->nodes) as $moduleNamespace) {
            $this->moduleNodesSave($moduleNamespace);
        }
    }

    /**
     * Сохранение всех загруженных или созданных узлов модуля
     *
     * @param $moduleNamespace
     */
    private function moduleNodesSave($moduleNamespace)
    {
        $moduleNodesInstances = $this->nodes[$moduleNamespace];

        foreach ($moduleNodesInstances as $nodeInstance => $nodes) {
            foreach (array_keys($nodes) as $nodePath) {
                $this->nodeSave($moduleNamespace, $nodePath, $nodeInstance);
            }
        }
    }

    /**
     * Сохранение узла если он был загружен и изменен или создан.
     * Если содержание оказалось пустым, то узел не создается, или если существует, удаляется
     *
     * @param        $moduleNamespace
     * @param        $nodePath
     * @param string $nodeInstance
     */
    private function nodeSave($moduleNamespace, $nodePath, $nodeInstance = '')
    {
        $data = $this->nodes[$moduleNamespace][$nodeInstance][$nodePath];

        // если узел был создан (не существовал до этого)
        if (isset($this->newNodes[$moduleNamespace][$nodeInstance][$nodePath])) {
            // и если его данные не пусты
            if ($data) {
                // создаем
                StorageModel::create([
                                         'module_namespace' => $moduleNamespace,
                                         'node_path'        => $nodePath,
                                         'node_instance'    => $nodeInstance,
                                         'data'             => j_($data),
                                     ]);

                // больше не считаем его новым
                unset($this->newNodes[$moduleNamespace][$nodeInstance][$nodePath]);

                // делаем оригинальное значение равным сохраненному
                $this->nodesOriginal[$moduleNamespace][$nodeInstance][$nodePath] = $data;
            }
        } else { // если узел существовал
            // и если был изменен
            if ($this->isNodeDirty($moduleNamespace, $nodePath, $nodeInstance)) {
                // если был изменен на непустое значение
                if ($data) {
                    // обновляем
                    StorageModel::where('module_namespace', $moduleNamespace)
                        ->where('node_path', $nodePath)
                        ->where('node_instance', $nodeInstance)
                        ->update(['data' => j_($data)]);
                } else { // если на пустое
                    // удаляем
                    StorageModel::where('module_namespace', $moduleNamespace)
                        ->where('node_path', $nodePath)
                        ->where('node_instance', $nodeInstance)
                        ->delete();
                }

                // делаем оригинальное значение равным сохраненному
                $this->nodesOriginal[$moduleNamespace][$nodeInstance][$nodePath] = $data;
            }
        }
    }

    /**
     * Проверка узла на измененность
     *
     * @param $moduleNamespace
     * @param $nodePath
     *
     * @return bool
     */
    private function isNodeDirty($moduleNamespace, $nodePath, $nodeInstance = '')
    {
        $originalExists = isset($this->nodesOriginal[$moduleNamespace][$nodeInstance][$nodePath]);

        // если нет оригинальной версии узла и производится эта проверка, то он определенно считается измененным
        if (!$originalExists) {
            return true;
        }

        return $this->nodes[$moduleNamespace][$nodeInstance][$nodePath] != $this->nodesOriginal[$moduleNamespace][$nodeInstance][$nodePath];
    }

    // todo

    /**
     * Перезагрузка всех узлов, или только всех узлов модуля, или только определенного узла
     *
     * @param string|bool|false $moduleNamespace
     * @param string|bool|false $nodePath
     */
    public function reload($moduleNamespace = false, $nodePath = false)
    {

    }

    private function reloadAll()
    {

    }

    private function moduleNodesReload()
    {

    }

    private function nodeReload($moduleNamespace, $nodePath)
    {

    }

}