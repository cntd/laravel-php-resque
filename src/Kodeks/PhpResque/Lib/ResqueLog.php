<?php  namespace Kodeks\PhpResque\Lib;

class ResqueLog {
    public static function log(\stdClass $std, ResqueWorkerEx $worker, $type = "error", $expire = 3600) {
        $job_id=$std->payload["id"];
        $log_job = $type . ":job:" . $job_id;
        $log_queues = []; 
        foreach($worker->getQueues() as $queue) {
            $log_queues[] = $type . ":queue:" . $queue . ":job:" . $job_id;
        }
        $log_pid = $type . ":pid:" . $worker->getPid() . ":job:" .$job_id;
        $data_json = json_encode($std);
        
        \Resque::redis()->muilti();
        \Resque::redis()->rpush($log_job, $data_json);
        \Resque::redis()->rpush($log_pid, $log_job);
        foreach ($log_queues as $log_queue) {
            \Resque::redis()->rpush($log_queue, $log_job);
            \Resque::redis()->expire($log_queue, $expire);
        }
        
        \Resque::redis()->expire($log_pid, $expire);
        \Resque::redis()->expire($log_job, $expire);
        \Resque::redis()->exec();
    }
    
    private static function removeBaseNamespace($key) {
        $splited = explode(":", $key);
        unset($splited[0]);
        return implode(":", $splited);
    }
    
    public static function getLog($type = "error") {
        $keys=\Resque::redis()->keys($type . ":job:*");
        $log = [];
        foreach($keys as $key) {
            $cleared_key = self::removeBaseNamespace($key);
            $records_values = \Resque::redis()->lrange($cleared_key, 0, -1);
            foreach($records_values as $rec_value) {
                $log[] = $rec_value;
            }
        }
        return $log; 
    }
    
    public static function getByQueue($type, $queue) {
        $keys=\Resque::redis()->keys($type . ":queue:" . $queue . ":*");
        $log = [];
        foreach($keys as $key) {
            $cleared_key = self::removeBaseNamespace($key);
            $records_indexs = \Resque::redis()->lrange($cleared_key, 0, -1);
            foreach($records_indexs as $record_index) {
                $records_values = \Resque::redis()->lrange($record_index, 0, -1);
                foreach($records_values as $record_value) {
                    $log[] = $record_value;
                }
            }
        }
        return $log; 
    }
    
    public static function getByPid($type, $queue) {
        $keys=\Resque::redis()->keys($type . ":pid:" . $queue . ":*");
        $log = [];
        foreach($keys as $key) {
            $cleared_key = self::removeBaseNamespace($key);
            $records_indexs = \Resque::redis()->lrange($cleared_key, 0, -1);
            foreach($records_indexs as $record_index) {
                $records_values = \Resque::redis()->lrange($record_index, 0, -1);
                foreach($records_values as $record_value) {
                    $log[] = $record_value;
                }
            }
        }
        return $log; 
    }  
}
