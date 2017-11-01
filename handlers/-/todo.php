<?php
//
//namespace \ewma\handlers;
//
//class Handlers extends \ewma\service\Service
//{
//    /**
//     * @var self
//     */
//    public static $instance;
//
//    /**
//     * @return \td\Svc
//     */
//    public static function getInstance()
//    {
//        if (null === static::$instance) {
//            $svc = new self;
//
//            static::$instance = $svc;
//            static::$instance->__register__();
//        }
//
//        return static::$instance;
//    }
//
//	/**
//	* var $nodes \ewma\handlers\Handlers\Nodes
//	*/
//    $nodes = \ewma\handlers\Handlers\Nodes::class;
//
//    //
//    //
//    //
//
//	public function compile()
//	{
//
//	}
//
//	public function render()
//	{
//
//	}
//}
//
//namespace \ewma\handlers\Handlers;
//
//class Nodes extends \ewma\service\Service
//{
//	public function get($id)
//	{
//
//	}
//}
//
////
//
//$handlersSvc = Handlers::getInstance();
//
//$handler = $handlersSvc->nodes->get(26);
//
//$handlersSvc->compile()