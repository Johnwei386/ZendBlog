<?php
namespace Backup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Backup\Zips\HZip;


class IndexController extends AbstractActionController
{   
    public function indexAction()
    {
        $dblog = $this->backupDatabase();
        $uplog = $this->backUploads();
        return new ViewModel(array(
            'dblogs' => $dblog,
            'uplogs' => $uplog,
        ));
    }
    
    public function backupDatabase()
    {
          //初始设置
        $adapter = $this->getServiceLocator()->get('DbAdapter');
        $qi = function($name) use ($adapter) { return $adapter->platform->quoteIdentifier($name); };
        $fp = function($name) use ($adapter) { return $adapter->driver->formatParameterName($name); };
        $qv = function($name) use ($adapter) { return $adapter->platform->quoteValue($name); };          
        
          //得到数据库表名称数组
        $metadata = $this->getServiceLocator()->get('DatabaseMeta');
        $tables = $metadata->getTableNames();
        
          //遍历并备份表
        $mysql = '';
        $log = '';
        foreach($tables as $table){
            $mysql .= "/*备份${table}表*/".PHP_EOL;
            $log .= "--备份${table}表--<br/>";
            $sql = 'SELECT * FROM '.$qi($table);
            $statement = $adapter->query($sql);
            $results = $statement->execute();
            if($results instanceof ResultInterface && $results->isQueryResult()){
                $resultSet = new ResultSet();
                $resultSet->initialize($results);
                $dataArr = $resultSet->toArray();
                foreach ($dataArr as $key=>$value){
                    if(is_array($value)){
                        $keys = array_keys($value);
                        $keys = array_map($qi, $keys);
                        $keys = implode(',', $keys);
                        $vals = array_values($value);
                        $vals = array_map($qv, $value);
                        $vals = implode(',', $vals);
                        $log .= 'INSERT INTO '.$qi($table)."(${keys}) values(${vals});"."<br/>";
                        $mysql .= 'INSERT INTO '.$qi($table)."(${keys}) values(${vals});".PHP_EOL;
                    }
                }
            }
            $mysql .= ''.PHP_EOL;
            $log .= "<br/>";
        }
        $log .= "^_^&nbsp;&nbsp;数据库备份完成！<br/>";
        $filename = ROOT_PATH . '/data/' . date('YmdHi') . ".sql";
        file_put_contents($filename, $mysql);
        return $log;
    }
    
    public function backUploads()
    {
        $sourceDir = ROOT_PATH.'/public/images/uploads';
        $destDir = ROOT_PATH.'/data/';
        $result = HZip::zipDir($sourceDir, $destDir.'uploads.zip');
        return $result;
    }
}