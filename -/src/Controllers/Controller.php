<?php namespace ewma\Controllers;

use ewma\App\App;
use ewma\Route\Route;
use ewma\Route\ResolvedRoute;
use ewma\Views\View;

class Controller
{
    public $app;

    public $__meta__;

    public $__logger__;

    public function __construct()
    {
        $this->app = App::getInstance();

        $this->__meta__ = new \ewma\Controllers\Controller\Meta($this);
    }

    public function __clone()
    {
        $this->__meta__ = clone $this->__meta__;

        $this->__meta__->setController($this);
    }

    public function __create()
    {

    }

    public function __recreate()
    {

    }

    public function __run()
    {

    }

    public function __done()
    {

    }

    /**
     * Доступность для вызова
     */
    const XHR = 0b00000001;
    const APP = 0b00000010;
    const RMT = 0b00000100;
    const ALL = 0b11111111;

    /**
     * Допустимые типы источников вызова
     *
     * @var int
     */
    public $allow = self::APP;

    /**
     * Если true, при повторном вызове будет возвращать уже созданный контроллер
     *
     * @var bool
     */
    public $singleton = false;

    /**
     * Данные, с которыми был вызван контроллер
     *
     * @var array
     */
    public $data = [];

    /**
     * @param string|false $path
     * @param null         $value
     *
     * @return mixed
     */
    public function data($path = false, $value = null)
    {
        if (null !== $value) {
            ap($this->data, $path, $value);

            return $this;
        } else {
            return ap($this->data, $path);
        }
    }

    protected function dataHas()
    {
        $rules = l2a(func_get_args());

        foreach ($rules as $rule) {
            if (false !== strpos($rule, '  ')) {
                $rule = preg_replace('/\s{2,}/', ' ', $rule);
            }

            list($path, $type) = array_pad(explode(' ', $rule), 2, null);

            $value = ap($this->data, $path);

            if (null === $value) {
                return false;
            } else {
                if (null !== $this && in($type, 'array, bool, float, int, numeric, object, resource, string, scalar')) {
                    if ($type == 'int') {
                        $is = ctype_digit($value);
                    } else {
                        $fn = 'is_' . $type;
                        $is = $fn($value);
                    }

                    if (empty($is)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function lock($console = true)
    {
        $this->__meta__->locked = true;

        if ($console) { // todo сделать чтобы зависело от какой-то настройки и доступа
            $this->console('lock controller ' . $this->__meta__->absPath);
        }
    }

    public function unlock()
    {
        $this->__meta__->locked = false;
    }

    /**
     * @param      $content
     * @param bool $path
     */
    public function log($content = '', $path = false)
    {
        if (null === $this->__logger__) {
            $this->__logger__ = new \ewma\Controllers\Controller\Logger($this);
        }

        if ($path) {
            $this->__logger__->setPath($path);
        }

        $this->__logger__->write($content);
    }

    /**
     * Уникальный идентификатор узла, расположенного по пути $path относительно текущего контроллера
     * ...
     *
     * @param bool|false $path
     *
     * @return string
     */
    public function _nodeId($path = false)
    {
        if (false === $path) {
            return $this->__meta__->nodeId;
        } else {
            if (false !== strpos($path, '|')) {
                list($path,) = explode('|', $path);
            }

            if (false !== strpos($path, ':')) {
                list($path,) = explode(':', $path);
            }

            list($modulePath, $nodePath) = $this->app->paths->separateAbsPath(
                $this->app->paths->resolve($path, $this->__meta__->absPath)
            );

            $module = $this->app->modules->getByPath($modulePath);

            return str_replace('\\', '_', $module->namespace) . '__' . str_replace('/', '_', $nodePath);
        }
    }

    public function _nodeInstance($path = false)
    {
        return path($this->_nodeId($path), $this->_instance());
    }

    /**
     * Уникальный идентификатор неймспейса узла, расположенного по пути $path относительно текущего контроллера
     *
     * @param bool|false $path
     *
     * @return string
     */
    public function _nodeNs($path = false)
    {
        if (false === $path) {
            return $this->__meta__->nodeNs;
        } else {
            list($modulePath,) = $this->app->paths->separateAbsPath(
                $this->app->paths->resolve($path, $this->__meta__->absPath)
            );

            $module = $this->app->modules->getByPath($modulePath);

            return str_replace('\\', '_', $module->namespace);
        }
    }

    public function _instance($force = false)
    {
        if ($force && !$this->__meta__->instance) {
            if ($force === true) {
                $this->__meta__->instance = k(8);
            } else {
                $this->__meta__->instance = $force;
            }
        }

        return $this->__meta__->instance;
    }

    public function instance_($instance)
    {
        $args = func_get_args();

        $instance = count($args) == 1 ? $args[0] : call_user_func_array('path', $args);

        $this->__meta__->instance = $instance;
    }

    /**
     * Абсолютный путь к узлу (или методу), расположенного по пути $path относительно текущего контроллера
     * ...
     *
     * @param bool|false $path
     *
     * @return string
     */
    public function _p($path = false)
    {
        return $this->app->controllers->renderAbsPath($path, $this);
    }

    public function _callerP($path = false)
    {
        return $this->_caller()->_p($path);
    }

    public function _calledMethodIn($list)
    {
        return in($this->__meta__->calledMethod, $list);
    }

    /**
     * Текущий модуль
     *
     * @return \ewma\Modules\Module
     */
    public function _module()
    {
        return $this->__meta__->module;
    }

    /**
     * Мастер-модуль
     *
     * @return \ewma\Modules\Module
     */
    public function _masterModule()
    {
        if (null === $this->__meta__->masterModule) {
            $this->__meta__->masterModule = $this->app->modules->getByPath($this->__meta__->module->masterModulePath);
        }

        return $this->__meta__->masterModule;
    }

    /**
     * Контроллер модуля (несуществующий, с выдуманным именем узла "-" и который находится якобы в корне текущего модуля)
     *
     * @return Controller
     */
    public function _moduleController()
    {
        return $this->__meta__->module->getController();
    }

    /**
     * Контроллер мастер-модуля (несуществующий, с выдуманным именем узла "-" и который находится якобы в корне мастер-модуля)
     *
     * @return Controller
     */
    public function _masterModuleController()
    {
        return $this->_masterModule()->getController();
    }

    /**
     * Конфигурация модуля или ее узел
     *
     * @param bool|string $path
     *
     * @return array|null
     */
    public function _config($path = false)
    {
        return ap($this->__meta__->module->config, $path);
    }

    /**
     * Конфигурация master-модуля или ее узел
     *
     * @param bool|string $path
     *
     * @return null
     */
    public function _masterConfig($path = false)
    {
        return ap($this->_masterModule()->config, $path);
    }

    /**
     * Конфигурация приложения (корневого модуля) или ее узел
     *
     * @param bool|string $path
     *
     * @return array|null
     */
    public function _appConfig($path = false)
    {
        return $this->app->getConfig($path);
    }

    public function _env($in = null)
    {
        $env = $this->app->getEnv();

        if (null == $in) {
            return $env;
        } else {
            return in($env, $in);
        }
    }

    public function _user($field = false)
    {
        $user = $this->app->access->getUser();

        if ($field) {
            if ($user) {
                return $user->model->{$field};
            }
        } else {
            return $user;
        }
    }

    public function isSuperuser()
    {
        return $user = $this->_user() and $user->isSuperuser();
    }

    public function a($permissionPattern = '')
    {
        return $user = $this->_user() and $user->hasPermission($this, $permissionPattern);
    }

    public function _nodeFilePath($path = false, $nodeType = false)
    {
        return $this->app->paths->getNodeFilePath($path, $nodeType, $this);
    }

    public function _nodeFileAbsPath($path = false, $nodeType = false) ////
    {
        return $this->app->paths->getNodeFilePath($path, $nodeType, $this);
    }

    /**
     * @param bool|array $input
     *
     * @return Call
     */
    public function _call($input = false)
    {
        $call = new Call($this);

        $call->set($input);

        return $call;
    }

    /**
     * @param bool|array $input
     *
     * @return Call
     */
    public function _abs()
    {
        $call = new Call($this);

        $args = func_get_args();

        if (count($args) == 1) {
            $call->set($args[0]);
        }

        if (count($args) == 2) {
            $call->path($args[0]);
            $call->data(false, $args[1]);
        }

        return $call->explode();
    }

    /**
     * @param bool|false $path
     *
     * @return Controller
     */
    public function _caller($path = false)
    {
        if ($path) {
            return $this->findCallerWithPath($this->_p($path), $this);
        }

        if ($this->__meta__->callerId) {
            return $this->app->controllers->getById($this->__meta__->callerId);
        } else {
            return $this->app->rootController;
        }
    }

    public function _methodAbsPath($methodName)
    {
        return $this->__meta__->absPath . ':' . $methodName;
    }

    private function findCallerWithPath($absPath, self $baseController)
    {
        if ($baseController->__meta__->callerId) {
            if ($absPath == '/' . $this->app->controllers->getById($baseController->__meta__->callerId)->__absPath__) {
                return $this->app->controllers->getById($baseController->__meta__->callerId);
            } else {
                return $this->findCallerWithPath($absPath, $baseController->_caller());
            }
        } else {
            return $this->app->rootController;
        }
    }

    public function _protected()
    {
        list($rightPath, $leftPath) = array_pad(array_reverse(func_get_args()), 2, null);

        $nodePath = false;

        if (false !== strpos($rightPath, ':')) {
            list($nodePath, $rightPath) = explode(':', $rightPath);
        }

        if (null === $leftPath) {
            $leftPath = 'tmp';
        }

        $nodeFilePath = $this->_nodeId($nodePath);

        return abs_path($leftPath, $nodeFilePath, $rightPath);
    }

    public function _public()
    {
        list($rightPath, $leftPath) = array_pad(array_reverse(func_get_args()), 2, null);

        $nodePath = false;

        if (false !== strpos($rightPath, ':')) {
            list($nodePath, $rightPath) = explode(':', $rightPath);
        }

        if (null === $leftPath) {
            $leftPath = 'files';
        }

        $nodeFilePath = $this->_nodeId($nodePath);

        return public_path($leftPath, $nodeFilePath, $rightPath);
    }

    public function _publicUrl()
    {
        list($rightPath, $leftPath) = array_pad(array_reverse(func_get_args()), 2, null);

        $nodePath = false;

        if (false !== strpos($rightPath, ':')) {
            list($nodePath, $rightPath) = explode(':', $rightPath);
        }

        if (null === $leftPath) {
            $leftPath = 'files';
        }

        $nodeFilePath = $this->_nodeId($nodePath);

        return abs_url($leftPath, $nodeFilePath, $rightPath);
    }

    /**
     * Вызов контроллера или метода контроллера
     *
     * @param bool|false        $path
     * @param array             $data
     * @param array|string|bool $dataMappings
     *
     * @return Controller|mixed
     */
    public function c($path = false, $data = [], $dataMappings = null)
    {
        if (null !== $dataMappings) {
            if (true === $dataMappings) {
                aa($data, $this->data);
            } else {
                remap($data, $this->data, $dataMappings);
            }
        }

        return $this->app->controllers->call($path, $data, $this);
    }

    public function async($path = false, $data = [], $dataMappings = null)
    {
        if (null !== $dataMappings) {
            if (true === $dataMappings) {
                aa($data, $this->data);
            } else {
                remap($data, $this->data, $dataMappings);
            }
        }

        if ('#' == substr($path, 0, 1)) {
            $callPath = '\ewma\handlers~:render';
            $callData = [
                'source' => substr($path, 1),
                'data'   => $data
            ];
        } else {
            $callPath = $this->_p($path);
            $callData = $data;
        }

        $command = 'nohup ./cli -j \'' . str_replace("'", "'\''", j_([$callPath, $callData])) . '\' >> ~/async.log 2>&1 &'; // todo /dev/null

        $this->log('ASYNC ' . $command);

        $cwd = getcwd();
        chdir($this->app->root);
        exec($command, $output);
        chdir($cwd);

        return $output;
    }

    function proc($path = false, $data = [], $dataMappings = null)
    {
        if (null !== $dataMappings) {
            if (true === $dataMappings) {
                aa($data, $this->data);
            } else {
                remap($data, $this->data, $dataMappings);
            }
        }

        if ('#' == substr($path, 0, 1)) {
            $callPath = '\ewma\handlers~:render';
            $callData = [
                'source' => substr($path, 1),
                'data'   => $data
            ];
        } else {
            $callPath = $this->_p($path);
            $callData = $data;
        }

        $process = $this->app->processDispatcher->create($callPath, $callData);

        return $process;
    }

    /**
     * @param $path
     *
     * @return Controller
     */
    public function n($path)
    {
        return $this->app->controllers->getNodeController($path, $this);
    }

    /**
     * Проксирование вызова на другой путь
     *
     * @param $path
     *
     * @return Controller|mixed
     */
    public function c_($path, $mergeData = [], $mergeMode = RA)
    {
        $data = $this->data;

        if ($mergeData) {
            if ($mergeMode == AA) {
                aa($data, $mergeData);
            }

            if ($mergeMode == RA) {
                ra($data, $mergeData);
            }
        }

        if ($mergeMode == RR) {
            $data = $mergeData;
        }

        return $this->_caller()->c($this->_p($path), $data);
    }

    /**
     * @param $path
     *
     * @return \ewma\SessionEvents\Dispatcher
     */
    public function e($path, $filter = null)
    {
        return $this->app->sessionEvents->getDispatcher($path, $filter, $this);
    }

    /**
     * @param $path
     *
     * @return \ewma\storageEvents\Dispatcher
     */
    public function se($path, $filter = null)
    {
        return $this->app->storageEvents->getDispatcher($path, $filter, $this);
    }

    /**
     * @param bool|string $path
     * @param mixed       $data
     * @param int         $mergeMode
     *
     * @return mixed|null
     */
    public function &d($path = false, $data = [], $mergeMode = AA) // todo сделать так же как у цсс (если первый массив то считать путь фэлсом...
    {
        $node = &$this->_dataNode($path, $data, $mergeMode, 'storage');

        return $node;
    }

    /**
     * @param bool|string $path
     * @param mixed       $data
     * @param int         $mergeMode
     *
     * @return mixed
     */
    public function &s($path = false, $data = [], $mergeMode = AA) // todo сделать так же как у цсс
    {
        $node = &$this->_dataNode($path, $data, $mergeMode, 'session');

        return $node;
    }

    /**
     * @param             $key
     * @param bool|string $path
     * @param mixed       $data
     * @param int         $mergeMode
     *
     * @return mixed|null
     */
    public function &otherS($key, $path = false, $data = [], $mergeMode = AA) // todo сделать так же как у цсс
    {
        $node = &$this->_dataNode($path, $data, $mergeMode, 'other_session', $key);

        return $node;
    }

    public function &dmap_($path, $mappings = '*')
    {
        $d = &$this->d($path);

        remap($d, $this->data, $mappings);

        return $d;
    }

    public function &smap_($path, $mappings = '*')
    {
        $s = &$this->s($path);

        remap($s, $this->data, $mappings);

        return $s;
    }

    public function &_dmap($path, $mappings = '*')
    {
        $d = &$this->d($path);

        remap($this->data, $d, $mappings);

        return $d;
    }

    public function &_smap($path, $mappings = '*')
    {
        $s = &$this->s($path);

        remap($this->data, $s, $mappings, true);

        return $s;
    }

    public function &dmap($path, $mappings = '*')
    {
        $this->dmap_($path, $mappings);

        $d = &$this->_dmap($path, $mappings);

        return $d;
    }

    public function &smap($path, $mappings = '*')
    {
        $this->smap_($path, $mappings);

        $s = &$this->_smap($path, $mappings);

        return $s;
    }

    /**
     * @param $path
     * @param $data
     * @param $mergeMode
     * @param $storageType
     *
     * @return mixed|null
     */
    private function &_dataNode($path, $data, $mergeMode, $storageType, $otherSessionKey = false)
    {
        list($path, $instance) = $this->app->controllers->explodeToPathAndInstance($path, $this);
        list($nodeFullPath, $dataPath) = array_pad(explode(':', $path), 2, '');

        $absPath = $this->_p($nodeFullPath);

        list($modulePath, $nodePath) = $this->app->paths->separateAbsPath($absPath);

        if ($storageType == 'session') {
            $node = &$this->app->session->getNode($modulePath, $nodePath, $instance);
        }

        if ($storageType == 'other_session') {
            $node = &$this->app->session->other($otherSessionKey)->getNode($modulePath, $nodePath, $instance);
        }

        if ($storageType == 'storage') {
            $node = &$this->app->storage->getNode($modulePath, $nodePath, $instance);
        }

        $dataNode = &ap($node, $dataPath);

        if ($data) {
            if ($mergeMode == AA) {
                aa($dataNode, $data);
            }

            if ($mergeMode == RA) {
                ra($dataNode, $data);
            }
        }

        if ($mergeMode == RR) {
            $dataNode = $data;
        }

        return $dataNode;
    }

    /**
     * @param bool|false $path
     * @param array      $data
     *
     * @return View
     */
    public function v($path = false, $data = [])
    {
        list($nodePath, $instance) = $this->app->controllers->explodeToPathAndInstance($path, $this);

        $v = $this->app->views->create($this->_nodeFilePath($nodePath, 'templates'), $data);

        $v->assign([
                       '__NODE_ID__'  => $this->_nodeId($nodePath),
                       '__INSTANCE__' => $instance
                   ]);

        return $v;
    }

    public function unpackModel($path = 'model')
    {
        return unpack_model($this->data($path));
    }

    public function unxpackModel($path = 'model')
    {
        return unxpack_model($this->data($path));
    }

    public function packModel($path = 'model')
    {
        return pack_model($this->data($path));
    }

    public function packModels()
    {
        $this->data = pack_models($this->data);
    }

    public function unpackModels()
    {
        $this->data = unpack_models($this->data);
    }

    public function xpackModel($path = 'model')
    {
        return xpack_model($this->data($path));
    }

    public function unpackCell($path = 'cell')
    {
        return unpack_cell($this->data($path));
    }

    public function unxpackCell($path = 'cell')
    {
        return unxpack_cell($this->data($path));
    }

    public function packCell($path = 'cell')
    {
        $cell = $this->data($path);

        if (is_string($cell)) {
            $cell = unpack_cell($cell);
        }

        if ($cell instanceof \ewma\Data\Cell) {
            return $cell->pack();
        }
    }

    public function xpackCell($path = 'cell')
    {
        $cell = $this->data($path);

        if (is_string($cell)) {
            $cell = unpack_cell($cell);
        }

        if ($cell instanceof \ewma\Data\Cell) {
            return xpack_cell($cell->model, $cell->field);
        }
    }

    public function unpackField($path = 'field')
    {
        return unpack_field($this->data($path));
    }

    public function unxpackField($path = 'cell')
    {
        return unxpack_field($this->data($path));
    }

    public function packField($path = 'cell')
    {
        $field = $this->data($path);

        if (is_string($field)) {
            $field = unpack_field($field);
        }

        if ($field instanceof \ewma\Data\Field) {
            return $field->pack;
        }
    }

    public function xpackField($path = 'cell')
    {
        $field = $this->data($path);

        if (is_string($field)) {
            $field = unpack_field($field);
        }

        if ($field instanceof \ewma\Data\Cell) {
            return xpack_field($field->model, $field->field);
        }
    }

    /**
     * @return \ewma\Css\Node
     */
    public function css()
    {
        $args = func_get_args();

        $callString = '';
        $vars = [];

        if (count($args) == 1) {
            if (is_array($args[0])) {
                $vars = $args[0];
            } else {
                $callString = $args[0];
            }
        }

        if (count($args) == 2) {
            $callString = $args[0];
            $vars = $args[1];
        }

        list($callString, $instance) = $this->app->controllers->explodeToPathAndInstance($callString, $this);

        list($relativeNodePath, $importPaths) = array_pad(explode(':', $callString), 2, null);

        $css = $this->app->css->provide($this, $relativeNodePath, $instance);

        if ($vars) {
            $css->setVars($vars);
        }

        if ($autoImportPaths = $this->_module()->lessAutoImport) {
            $css->import($autoImportPaths, $this->_moduleController());
        }

        if (!empty($importPaths)) {
            $css->import($importPaths);
        }

        return $css;
    }

    public function js()
    {
        $args = func_get_args();

        $path = isset($args[0]) ? $args[0] : false;

        if (starts_with($path, ['http://', 'https://'])) {
            $this->app->js->provideUrl($path);
        } else {
            $callArgs = array_slice($args, 1);

            // todo js instance
            list($path, $instance) = $this->app->controllers->explodeToPathAndInstance($path, $this);

            list($relativeNodePath, $callString) = array_pad(explode(':', $path), 2, null);

            $this->app->js->provide($this, $relativeNodePath);

            if (null !== $callString) {
                $this->app->js->addCall($this, $relativeNodePath, $callString, $callArgs);
            }
        }
    }

    public function jsCall()
    {
        $args = func_get_args();

        if (isset($args[0])) {
            $callString = $args[0];
            $callArgs = array_slice($args, 1);

            $this->app->js->addCall($this, false, $callString, $callArgs);
        }
    }

    /**
     * @param $code
     */
    public function jsRaw($code)
    {
        $this->app->js->addRaw($code);
    }

    /**
     * [[[jsFileNodePath:]selector:]instance] // todo переделать палку в двоеточие:
     *
     * нет двоеточий:   селектор - ...
     * одно двоеточие:  селектор, инстанс - ...
     * два двоеточия:   загрузка файла, селектор, инстанс - ...
     *
     * @param string|false $input
     *
     * @return \ewma\Js\JqueryBuilder
     */
    public function jquery($input = false)
    {
        $relativeNodePath = false;

        if (false !== strpos($input, ':')) { // todo придумать какой-нибудь символ, который будет только менять базовый путь для селектора, но не загружать файл для этого пути
            list($relativeNodePath,) = explode(':', $input);

            $this->js($relativeNodePath);
        }

        $jqueryBuilder = $this->app->js->addJqueryBuilder($this, $relativeNodePath, $this->_selector($input));

        return $jqueryBuilder;
    }

    /**
     * @param string|false $input // todo проблема палки
     *
     * @return string
     */
    public function _selector($input = false)
    {
        $output = [];

        $parts = l2a($input);

        foreach ($parts as $part) {
            $relativeNodePath = false;
            $selectorString = $part;

            if (false !== strpos($part, ':')) {
                list($relativeNodePath, $selectorString) = explode(':', $selectorString);
            }

            $nodeId = $this->_nodeId($relativeNodePath);

            list($selector, $instance) = $this->app->controllers->explodeToPathAndInstance($selectorString, $this);

            if (!$selector) {
                $selector = '.' . $nodeId;
            } else {
                if ($selector == '.' || $selector == '#') {
                    $selector .= $nodeId;
                } else {
                    if (!preg_match('/^[a-z0-9_-]$/i', substr($selector, 1, 1))) {
                        $selector = substr($selector, 0, 1) . $nodeId . substr($selector, 1);
                    }
                }
            }

            if ('' !== $instance) {
                $selector .= "[instance='" . $instance . "']";
            }

            $output[] = $selector;
        }

        return implode($output);
    }

    /**
     * @return mixed
     */
    public function widget()
    {
        $args = func_get_args();

        $jqueryBuilder = $this->jquery(isset($args[0]) ? $args[0] : false);

        // добавил ретурн но не проверял
        return call_user_func_array([$jqueryBuilder, 'widget'], array_slice($args, 1));
    }

    public function _w($path)
    {
        return $this->_selector($path) . '|' . $this->_nodeId($path);
    }

    /**
     * Передает значение в консоль браузера
     *
     * @param $input
     */
    public function console($input) // todo app->response
    {
        $this->app->response->console($input);
    }

    public function _cookie($name) // todo app->request
    {
        return ap($_COOKIE, $name);
    }

    /**
     * @param      $name
     * @param null $value
     * @param bool $timeout
     * @param bool $path
     */
    public function cookie_($name, $value = null, $timeout = false, $path = false) // todo app->response
    {
        $this->app->response->cookie($name, $value, $timeout, $path);
    }

    private function _allowed()
    {
        if ($this->app->mode == App::REQUEST_MODE_CLI) {
            return true;
        }

        if ($this->allow == self::ALL) {
            return true;
        }

        $allow = $this->allow;

        if (is_array($this->allow)) { // php <5.6
            if (!empty($this->allow)) {
                foreach ($this->allow as $allowType) {
                    if (isset($allow)) {
                        $allow |= $allowType;
                    } else {
                        $allow = $allowType;
                    }
                }
            } else {
                $allow = self::APP;
            }
        }

        if ($this->_caller()->__meta__->allowForCallPerform & $allow) {
            return true;
        }

        $this->console('not allow ' . $this->_caller()->_nodeId() . ' from ' . ($this->__meta__->virtual ? 'virtual ' : '') . $this->_nodeId());
    }

    public function bindCall($path, \Closure $callback) // todo подумать как применить, надо/ненадо, доделать/выбросить...
    {
        $this->app->events->bindCall($this, $path, $callback);
    }

    public function __run__($method)
    {
        $allowed = $this->_allowed();
        $locked = $this->__meta__->locked;

        if ($allowed && !$locked) {
            $this->__run();

            if (method_exists($this, $method)) {
                $output = call_user_func_array([$this, $method], $this->__meta__->args);
            } else {
                $output = null;

                $message = $this->__meta__->virtual ? 'Virtual controller' : 'Controller';
                $message .= ' "' . $this->__meta__->absPath . '"';
                $message .= ' does not have method "' . $method . '"';

                $this->app->rootController->console($message);
            }

            if ($this->app->events->hasOnceCallBinding) {
                $this->app->events->triggerCallBinding($this, $method, $this->__meta__->args);
            }

            $this->__done();

            return $output;
        }
    }

    /**
     * @param bool          $pattern
     * @param bool|\Closure $matchCallback
     *
     * @return ResolvedRoute|\BlackHole
     */
    public function route($pattern = false, $matchCallback = false)
    {
        if ($this->__meta__->routeResponse) {
            return new \BlackHole;
        }

        $meta = &$this->__meta__;

        if (null === $meta->route) {
            $meta->route = $this->app->route;
        }

        $route = new Route($meta->baseRoute, $meta->route, $pattern);

        $resolved = $route->match($matchCallback);

        if (null === $resolved) {
            return new \BlackHole;
        } else {
            return new ResolvedRoute(
                $this,
                $resolved['data'],
                $resolved['route'],
                $resolved['base_route']
            );
        }
    }

    public function routeResponse()
    {
        $response = $this->__meta__->routeResponse;

        $this->__meta__->routeResponse = null;

        return $response;
    }

    public function _route($appendPath = false)
    {
        return '/' . path($this->__meta__->baseRoute, $appendPath);
    }
}
