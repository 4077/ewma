<?php namespace ewma\dev\svcs\controllers;

class Modules extends \Controller
{
    public $singleton = true;

    private $tree;

    private function getCacheFilePath()
    {
        return $this->_protected('cache', '\dev\project services/project_tree:modules.json'); // пока берется из старого модуля
    }

    public function getTree()
    {
        if (!$this->tree) {
            $cacheFilePath = $this->getCacheFilePath();

            if (!file_exists($cacheFilePath)) {
//                $this->updateCache();
            }

            $t1 = $this->tree = jread($cacheFilePath);

            $t2 = $this->app->modules->getCache();
        }

        return $this->tree;
    }

//    //
//
//    private $modulesPathsFoundOnUpdateCache = [];
//
//    public function updateCache($modulePath = '')
//    {
//        $cacheFilePath = $this->_protected('cache/modules.json');
//
//        $tree = $this->updateCacheRecursion(p2a($modulePath));
//
//        if ($modulePath) {
//            $this->tree = jread($cacheFilePath);
//
//            $nodePath = $modulePath;
//
//            ap($this->tree, $nodePath, $tree);
//
//            jwrite($cacheFilePath, $this->tree);
//        } else {
//            jwrite($cacheFilePath, $tree);
//
//            $this->c('session')->gc($this->modulesPathsFoundOnUpdateCache);
//        }
//    }
//
//    private function updateCacheRecursion($modulePathArray)
//    {
//        $modulePath = a2p($modulePathArray);
//
//        $this->modulesPathsFoundOnUpdateCache[] = $modulePath;
//
//        $moduleDir = abs_path($modulePath ? 'modules' : '', $modulePath);
//
//        $node['-']['settings'] = ['type' => 'master'];
//
//        if ($modulePathArray) {
//            ra($node['-']['settings'], $this->getModuleSettings($moduleDir));
//        }
//
//        $node['-']['nodes'] = $this->getModuleNodesTree($moduleDir);
//        $node['-']['models'] = $this->getModuleModelsTree($moduleDir);
//
//        $nestedModulesDir = $modulePath ? $moduleDir : abs_path('modules');
//
//        foreach (new \DirectoryIterator($nestedModulesDir) as $fileInfo) {
//            if ($fileInfo->isDot()) {
//                continue;
//            }
//
//            if ($fileInfo->isDir()) {
//                $fileName = $fileInfo->getFilename();
//                if ($fileName != '-') {
//                    $modulePathArray[] = $fileName;
//                    $node[$fileName] = $this->updateCacheRecursion($modulePathArray);
//                    array_pop($modulePathArray);
//                }
//            }
//        }
//
//        return $node;
//    }
//
//    // settings
//
//    public function getModuleSettings($moduleDirAbsPath)
//    {
//        return require_once $moduleDirAbsPath . '/settings.php';
//    }
//
//    // nodes tree
//
//    private $nodesTreeOutput = [];
//
//    private function getModuleNodesTree($moduleDir)
//    {
//        $this->nodesTreeOutput = [];
//
//        // files
//        foreach (l2a('controllers, js, css, less, templates') as $type) {
//            $ext = $type;
//
//            if ($type == 'controllers') {
//                $ext = 'php';
//            }
//
//            if ($type == 'templates') {
//                $ext = 'tpl';
//            }
//
//            $this->getModuleNodesTreeTypeRecursion('/' . path($moduleDir, '-', $type), $ext, []);
//        }
//
//        // session, storage
//        $moduleNamespace = $this->getModuleNamespace($moduleDir);
//
//        if ($moduleNamespace) {
//            $sessions = Session::where('module_namespace', $moduleNamespace)->get();
//            foreach ($sessions as $session) {
//                aa($this->nodesTreeOutput, [$session['node_path'] . '/./session' => ['not_empty' => !empty($session['data'])]]);
//            }
//
//            $storages = Storage::where('module_namespace', $moduleNamespace)->get();
//            foreach ($storages as $storage) {
//                aa($this->nodesTreeOutput, [$storage['node_path'] . '/./storage' => ['not_empty' => !empty($storage['data'])]]);
//            }
//        }
//
//        return $this->nodesTreeOutput;
//    }
//
//    private function getModuleNodesTreeTypeRecursion($nodeTypeDir, $ext, $nodePathArray)
//    {
//        $nodePath = a2p($nodePathArray);
//
//        $nodeDir = abs_path($nodeTypeDir, $nodePath);
//
//        if (is_dir($nodeDir)) {
//            foreach (new \DirectoryIterator($nodeDir) as $fileInfo) {
//                if ($fileInfo->isDot()) {
//                    continue;
//                }
//
//                if ($fileInfo->isDir()) {
//                    $nodePathArray[] = $fileInfo->getFilename();
//                    aa($this->nodesTreeOutput, [a2p($nodePathArray) => []]);
//                    $this->getModuleNodesTreeTypeRecursion($nodeTypeDir, $ext, $nodePathArray);
//                    array_pop($nodePathArray);
//                }
//
//                if ($fileInfo->isFile()) {
//                    if ($fileInfo->getExtension() == $ext) {
//                        $nodePathArray[] = $fileInfo->getBasename('.' . $ext);
//                        aa($this->nodesTreeOutput, [path(a2p($nodePathArray), '.', $ext) => []]);
//                        array_pop($nodePathArray);
//                    }
//                }
//            }
//        }
//    }
//
//    //
//
//    public function getModuleNamespace($module_abs_path)
//    {
//        if (file_exists($module_abs_path . '/settings.php')) {
//            $module_config = require $module_abs_path . '/settings.php';
//
//            return $module_config['namespace'];
//        }
//    }
//
//    // models tree
//
//    private function getModuleModelsTree($module_abs_path)
//    {
//        return $this->get_module_models_tree_recursion($module_abs_path . '/models');
//    }
//
//    private function get_module_models_tree_recursion($models_dir_path, $node_path = [])
//    {
//        $node_path_str = $node_path ? '/' . implode('/', $node_path) : '';
//
//        if (is_dir($models_dir_path . $node_path_str)) {
//            $nodes = scandir($models_dir_path . $node_path_str);
//
//            $output = [];
//
//            foreach ($nodes as $node) {
//                if ($node != '.' && $node != '..') {
//                    if (is_dir($models_dir_path . $node_path_str . '/' . $node)) {
//                        $node_path[] = $node;
//                        $output[$node] = $this->get_module_models_tree_recursion($models_dir_path, $node_path);
//                        array_pop($node_path);
//                    }
//
//                    if (is_file($models_dir_path . $node_path_str . '/' . $node)) {
//                        $path_info = pathinfo($models_dir_path . $node_path_str . '/' . $node);
//
//                        $output['.'][] = $path_info['filename'];
//                    }
//                }
//            }
//
//            return $output;
//        }
//    }
}
