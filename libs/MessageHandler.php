<?php

namespace xzchat\libs;


class MessageHandler
{
    /**
     * 用户信息处理
     * @param ConnectCollection $connector
     * @param $frame
     * @param bool $isDoFork
     */
    static public function msgDeal(&$connector, &$frame, $isDoFork = true)
    {
        if ($isDoFork) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                //echo 'could not fork生成子进程失败';
            } elseif ($pid == 0) {
                echo '当前内存使用量：' . memory_get_usage(true) . PHP_EOL;
                echo '当前子进程pid：' . posix_getpid() . PHP_EOL;

                try{
                    self::distributor($connector, $frame);
                }catch (\Exception $exception){
                    echo "信息分发错误，错误信息：" . $exception->getMessage() . PHP_EOL;
                }

                //处理完信息之后杀死进程
                posix_kill(posix_getpid(), SIGTERM);
            } else {
                //echo "I'm the parent process 子进程的pid值：{$pid} \n";
            }
        } else {
            self::distributor($connector, $frame);
        }
    }

    static function parseData($frameData)
    {
        return $receiveInfo = json_decode($frameData, true);
    }

    /**
     * 信息分发处理
     * @param $connector
     * @param $frame
     */
    static function distributor(&$connector, &$frame)
    {
        echo str_repeat('=', 20) . PHP_EOL;

        //信息处理
        echo "收到的信息: {$frame->data}" . PHP_EOL;
        echo "链接id: {$frame->fd}" . PHP_EOL;

        $data = self::parseData($frame->data);//传回来的信息是json

        //消息分发器构建
        $messageDistributor = $connector->messageDistributor;
        /** @var MessageDistributor $distributor */
        $distributor = new $messageDistributor($connector, $frame, $data);
        $distributor->run();

        echo str_repeat('=', 20) . PHP_EOL;

        //发送信息后事件处理
        $connector->triggerEvent('afterMessage', [&$connector->server, &$frame]);
    }
}