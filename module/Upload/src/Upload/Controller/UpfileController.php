<?php
namespace Upload\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Upload\Form\UploadForm;

class UpfileController extends AbstractActionController
{
    public function uploadAction()
    {
       $form = new UploadForm('uploadFiles');
       
       $request = $this->getRequest();
       if($request->isPost()){
           // Make certain to merge the files info!
           $post = array_merge_recursive(
               $request->getPost()->toArray(),
               $request->getFiles()->toArray()
               );
           
           $form->setData($post);
           if($form->isValid()){
               $data = $form->getData();
               $filename = $data['files']['name'];
               if(is_uploaded_file($data['files']['tmp_name'])){
                   $sour_path = $data['files']['tmp_name'];
                   $re_filename = "/images/uploads/".time()."-".mt_rand(0, 1000)."-".$filename;
                   $des_path = ROOT_PATH."/public".$re_filename;
                   if(move_uploaded_file($sour_path, $des_path)){
                       
                             //缩放图片
                       $tmp_img = ROOT_PATH."/public/images/uploads/".md5(mt_rand(1,300).time());
                       if($this->clipImg($des_path, 847, $tmp_img)){
                                 //删除源文件,将转换后的图像重命名回源文件
                           unlink($des_path);
                           rename($tmp_img, $des_path);
                            }                            
                       return new JsonModel(array(
                           'status' => true,
                           'filepath' => $re_filename,
                             ));
                   }
               }                              
           } else {
               return new JsonModel(array(
                   'status' => false,
                   'formErrors' => $form->getMessages(),
                   ));
           }
       }       
       return new ViewModel(array(
           'form' => $form,
       ));
    }
    
    public function modheadAction()
    {
        $request = $this->getRequest();
          //判断是否ajax请求，javascript--xmlHttpRequest请求
        if ($request->isXmlHttpRequest()) {
            if($request->isPost()){
                $receive = $request->getPost();
                    //删除空格
                $receive['headimg'] = preg_replace('{( )+}', '', $receive['headimg']);
                    //是否存在扩展名
                if(!preg_match('/.+\.((jpg)|(png)|(gif)|(jpeg)$)/', $receive['headimg'])){
                    return new JsonModel(array(
                        'status'=>false,
                        'message'=>"修改名称失败，请检查输入的文件名是否正确",
                    ));
                }
                
                    //使用正则来修改XML文件
                /* $xmlfile = file_get_contents(CONFIG_PATH.DS.'config.xml');
                $replacement = '<headimg>'.$receive['headimg'].'</headimg>';
                $modfile = preg_replace('/<headimg>(.*?)<\/headimg>/', $replacement, $xmlfile);
                if(false === file_put_contents(CONFIG_PATH.DS.'config.xml', $modfile)){
                    return new JsonModel(array(
                        'status'=>false,
                        'message'=>"修改名称失败，请稍后重拾",
                    ));
                } */
                
                    //使用DOM来修改XML文件
                $xmlDoc = new \DOMDocument('1.0', 'UTF-8');//生成DOM解析器
                $xmlfile = CONFIG_PATH.DS.'config.xml';
                $xmlDoc->load($xmlfile);//得到一颗XML文档树实例
                $headimg = $xmlDoc->getElementsByTagName("headimg")->item(0);//得到headimg的首个节点
                $headimg->nodeValue = $receive['headimg'];
                if(false === $xmlDoc->save($xmlfile)){
                    return new JsonModel(array(
                        'status'=>false,
                        'message'=>"修改名称失败，请稍后重拾",
                    ));
                }
                
                return new JsonModel(array(
                    'status'=>true,
                    'message'=>"修改名称成功",
                ));
            }
        } else {
            return $this->redirect()->toRoute('upfiles');
        }
    }
    
    public function upfileBrowserAction()
    {
        $filepath = ROOT_PATH."/public/images/uploads";
        $files = $this->getFiles($filepath);
        
          //得到头像名称
        $config = new \Zend\Config\Reader\Xml();
        $allconfig = $config->fromFile(CONFIG_PATH.DS.'config.xml');
        $headname = $allconfig['headimg'];
        return array(
            'files' => $files,
            'headname' => $headname,
        );
    }
    
    public function renameAction()
    {
        $filepath = ROOT_PATH."/public/images/uploads";
        $receive = $this->getRequest()->getPost();
          
          //删除空格
        $receive['newname'] = preg_replace('{( )+}', '', $receive['newname']);
        
        if(empty($receive['newname'])){
            return new JsonModel(array(
                'status' => false,
                'errorcode' => 1,
                'message' => "文件名不能为空",
            ));
        }
        
        if(empty(substr($receive['newname'],0,strpos($receive['newname'], '.')))){
            if(strpos($receive['newname'], '.') === 0){
                return new JsonModel(array(
                    'status' => false,
                    'errorcode' => 2,
                    'message' => "文件名不能只有扩展名",
                ));
            }
        }
        
        $sourFilename = $filepath."/".$receive['filename'];
        $destFilename = $filepath."/".$receive['newname'];        
        if(!file_exists($sourFilename)){
            return new JsonModel(array(
                'status' => false,
                'errorcode' => 3,
                'message' => "源文件不存在",
            ));
        }
        
        
        $dest_ext = substr($receive['newname'], strrpos($receive['newname'], '.')+1);
        $sour_ext = substr($receive['filename'], strrpos($receive['filename'], '.')+1);
        if($dest_ext !== $sour_ext){
            $destFilename = $destFilename.".".$sour_ext;
        }
        
        if(rename($sourFilename, $destFilename)){
            return new JsonModel(array(
                'status' => true,
            ));
        }else {
            return new JsonModel(array(
                'status' => false,
                'errorcode' => 4,
                'message' => "文件重命名出错",
            ));
        }
    }
    
    public function delfileAction()
    {
        $filepath = ROOT_PATH."/public/images/uploads";
        $receive = $this->getRequest()->getPost();
        if($receive['confirm'] == 'yes'){
            if(unlink($filepath."/".$receive['filename'])){
                return new JsonModel(array(
                    'status' => true,
                    'message' => '删除文件成功！',
                ));
            }else {
                return new JsonModel(array(
                    'status' => false,
                    'message' => '删除文件失败！',
                ));
            }
        }        
    }
    
    //遍历目录，得到文件名
    public function getFiles($basepath)
    {
        $iterator = new \DirectoryIterator($basepath);
        $files = array();
        foreach ($iterator as $file) {
            if ($file->isFile()) {
               $files[$file->getFilename()] = array(
                   'createTime' => date("Ymd-H:i:s",$file->getCTime()),
                   'fileSize' => $file->getSize(),
               );
            }            
        }
        return $files;
    }    
    
     //制作缩略图
    private function clipImg($imagename,$stdWidth,$outfile)
    {
          //如果文件不存在，返回假
        if(!file_exists($imagename)) return false;
        
          //宽度判断,小于标准宽度则不裁切
        $img_info = getimagesize($imagename);
        $src_w = $img_info[0];
        $src_h = $img_info[1];
        if($src_w < $stdWidth) return false;
        
          //得到裁切的高度,固定宽度，高度等比例变化
        $h = floor(($stdWidth/$src_w)*$src_h);//高度下取整
        
          //创建原图资源
        $ext = image_type_to_extension($img_info[2]);
        $fun = "imagecreatefrom".substr($ext, 1);
        $img = $fun($imagename);      
        
          //创建底图资源
        if($ext == '.gif'){
            $zoom_img = imagecreate($stdWidth, $h);
        }else {
            $zoom_img = imagecreatetruecolor($stdWidth, $h);
        } 
        
          //透明色处理
        if($ext == ".png") {
                    // 关闭 alpha 渲染并设置 alpha 标志
                imagealphablending($zoom_img, false); //不启用混色模式
                $colorTransparent = imagecolorallocatealpha($zoom_img, 0, 0, 0, 127);//设置底图颜色，完全透明
                imagefill($zoom_img, 0, 0, $colorTransparent);
                imagesavealpha($zoom_img, true);//保留alpha通道到png图片中，处理png图片需要加上
        }elseif($ext == ".gif") {
                $trnprt_indx = imagecolortransparent($img);//得到gif透明色索引，无透明色返回-1
                if ($trnprt_indx >= 0) {
                    $trnprt_color = imagecolorsforindex($img, $trnprt_indx);//由透明色索引得到颜色值
                    $trnprt_indx = imagecolorallocate($zoom_img, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
                    imagefill($zoom_img, 0, 0, $trnprt_indx);
                    imagecolortransparent($zoom_img, $trnprt_indx);//保留源透明色
                }
        }else {
            Imagefill($zoom_img, 0, 0, imagecolorallocate($zoom_img, 255, 255, 255));
        } 
        
          //创建缩略图
        imagecopyresampled($zoom_img, $img, 0, 0, 0, 0, $stdWidth, $h, $src_w, $src_h);   
          
          //输出文件
        $func = "image".substr($ext, 1);
        $func($zoom_img,$outfile);
        
          //销毁资源
        imagedestroy($img);
        imagedestroy($zoom_img);
        
        return true;
    }
}

/***********************************************************************************************
 * PRG插件是指File Post-Redirect-Get控制器插件，它被用来处理当一个表单中存在有多个
 * input时，当有一个input不能通过form认证时，文件上传的input也将不可用，这时需要重新
 *  上传和命名，这很繁琐。 这个插件就是用来管理文件上传input，保存可用的上传文件直到整个
 * form表单认证是通过的。它的工作流程是：
 * 1.运行form表单的filter，也就是重命名上传文件操作，然后从临时存储目录移出文件。
 * 2.存储整个请求中可用的POST数据到session中
 * 3.更改任何已经通过认证的文件上传的请求标志位(flag)为false，则当表单重新提交而没有上传
 *   文件时不会导致认证错误。
 *
 ***********************************************************************************************/
    
/****************************************************************************************************
 *  PRG方法
 *  
        $form = new UploadForm('upload-form');
        $tempFile = null;
        $prg = $this->fileprg($form);
        if($prg instanceof \Zend\Http\PhpEnvironment\Response){
            return $prg; //Return PRG redirect response
        } elseif (is_array($prg)) {
            if($form->isValid()){
                $data = $form->getData();
                // Form is valid,save the form!
                return $this->redirect()->toUrl('upload/success');
            } else {
                // Form not valid, but file uploads might be valid...
                // Get the temporary file information to show the user in the view
                $fileErrors = $form->get('image-file')->getMessages();
                if (empty($fileErrors)) {
                    $tempFile = $form->get('image-file')->getValue();
                }
            }
        }
        
         return new ViewModel(array(
            'form' => $form,
            'tempFile' => $tempFile,
        ));
 ***********************************************************************************************************/
    
/**********************************************************************************************************
 * 完整操作
 * 
       $form = new UploadForm('upload-form');
       
       $request = $this->getRequest();
       if($request->isPost()){
           // Make certain to merge the files info!
           $post = array_merge_recursive(
               $request->getPost()->toArray(),
               $request->getFiles()->toArray()
               );
           
           $form->setData($post);
           if($form->isValid()){
               $data = $form->getData();
               
               $filename = $data['image-file']['name'];
               $suffix = substr($filename,strrpos($filename, '.')+1);
               $compareArr = array('jpg','jpeg','png','gif');
               if(!in_array($suffix, $compareArr)){
                       echo "<script>alert('上传文件必须为标准的图片格式，请返回重传！');history.go(-1)</script>";
                       die();
                   }
                   
               if(is_uploaded_file($data['image-file']['tmp_name'])){
                  $sour_path = $data['image-file']['tmp_name'];
                  $des_filename = "/public/images/uploads/".date('Ymd')."-".mt_rand(0, 2000)."-".$filename;
                  $des_filename = ROOT_PATH.$des_filename;
                  if(move_uploaded_file($sour_path, $des_filename)){
                         return $this->redirect()->toUrl('upload/success');
                  }else{
                         echo "<script>alert('上传文件失败，请返回重传！');history.go(-1)</script>";
                    }
                }
           }
       }
       
       return new ViewModel(array(
           'form' => $form,
       ));
 */
