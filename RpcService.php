<?php

require_once __DIR__ . '/api/components/Message.php';
require_once __DIR__ . '/api/components/Response.php';

class RpcService
{
    public static $config = [
        'path' => ''  // 服务目录
    ];

    /**
     * @param $config
     */
    public static function config($config) {
        self::$config = $config;
    }

    /**
     * 执行方法
     * @param $class
     * @param $method
     * @param $params
     */
    public static function execMethod($class, $method, $params) {
        if($class && $method) {
            try {
                $callable = self::isCallable($class, $method);
                $argv = self::parseParameters($class, $method, $params);
            } catch (Exception $e) {
                return Response::error($e->getCode() ?: Message::SYSTEM_ERROR, $e->getMessage());
            }
            // 调用类的方法
            try {
                $object = new $class();
                $result = call_user_func_array(array($object, $method), $argv);
                //把运行后的结果返回给客户端
                return $result;
            } // 有异常
            catch (Exception $e) {
                return Response::error(Message::SYSTEM_ERROR);
            }
        } else {
            return Response::error(Message::CLASS_OR_METHOD_ERROR);
        }
    }

    /**
     * 检查是否可调用
     * @param $class
     * @param $method
     * @return bool
     * @throws Exception
     */
    private static function isCallable($class, $method) {
        // 首字母转为大写
        $class = ucfirst($class);
        // 判断类对应文件是否载入，如果有，则引入文件
        if (isset(self::$config['path']) && !empty(self::$config['path'])) {
            $path = rtrim(self::$config['path'], '/');
        } else {
            $path = __DIR__;
        }
        $include_file = $path . '/' . $class . '.php';
        if(file_exists($include_file)) {
            require_once $include_file;
            if(!class_exists($class) || !method_exists($class, $method)) {
                throw new Exception('', Message::CLASS_OR_METHOD_ERROR);
            }
        } else {
            throw new Exception('', Message::CLASS_OR_METHOD_ERROR);
        }
        return true;
    }

    /**
     * @param $class
     * @param $method
     * @param $argv
     * @return array
     * @throws \Exception
     */
    private static function parseParameters($class, $method, $argv): array
    {
        $reflector = new \ReflectionMethod($class, $method);
        $params = [];
        foreach ($reflector->getParameters() as $i => $parameter) {
            if (isset($argv[$i])) {
                self::checkParameterType($i, $argv[$i], $parameter);
                $value = $argv[$i];
            } else {
                if ($parameter->isOptional()) {
                    $value = $parameter->getDefaultValue();
                } else {
                    throw new Exception("param {$i} {$parameter->name} is empty", Message::SYSTEM_ERROR);
                }
            }
            $params[$parameter->name] = $value;
        }
        return $params;
    }

    /**
     * @param $i
     * @param $argv
     * @param $parameter
     * @throws Exception
     */
    private static function checkParameterType($i, $argv, $parameter) {
        $reflectorParameterType = $parameter->getType();
        if ($reflectorParameterType) {
            $reflectorParameterType = self::parseType($reflectorParameterType);
            $argvType = self::parseType(gettype($argv));
            if ($argvType != $reflectorParameterType) {
                throw new Exception("param {$i} {$parameter->name} is {$argvType} it should be {$reflectorParameterType}", Message::SYSTEM_ERROR);
            }
        }
    }

    /**
     * @param $type
     * @return mixed|string
     */
    private static function parseType($type) {
        switch ($type) {
            case 'integer':
                return 'int';
            case 'double':
                return 'float';
            default:
                return $type;
        }
    }
}