<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		// $total_q = $this->db->query("select * from hot_video");
		// $total_s = $total_q->result_array();


		// $this->load->view('welcome_message',array(
		// 	'list' => $total_s
		// ));
	}

	public function myfollows()
	{
		// $data = '';
		// if (!empty($GLOBALS['HTTP_RAW_POST_DATA']))
		// {
		//     $data =  isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
		//     //$j =json_decode( $command,true);//true,转化成数组
		// }

		

		$data = file_get_contents("php://input");
		if($data != null){
			// log_message("error",$data);
			$json_array =  json_decode($data,true);
			if($json_array !== false){
				$feeds = $json_array['feeds'];
				if(is_array($feeds) && count($feeds)){

					foreach ($feeds as $key => $value) {
						//type 1:图片或者视频
						if($value['type'] == '1'){
							$photo_id = $value['photo_id'];
							$video_not_exist = $this->check_photo_id_exist($photo_id);
							if($video_not_exist === false && array_key_exists('photo_id', $value)){
								//video or images
								try{
									$mtype = intval($value['ext_params']['mtype']);
									//需要判断 ext_params -> mtype,3:短视频，6 图片
									$video_img_url = $value['cover_thumbnail_urls'][0]['url'];
									$mv_photo_cdns = "";
									if($mtype == 3){
										//视频
										$mv_photo_cdns = $value['main_mv_urls'][1]['url'];
									}else if($mtype == 6){

										$mv_photo_cdns = array();
										$ext_params = $value['ext_params'];
										if(array_key_exists('atlas', $ext_params)){
											$ext_params_atlas = $value['ext_params']['atlas'];
											$host = $ext_params_atlas['cdn'][1];
											$list = $ext_params_atlas['list'];
											foreach ($list as  $photo_str) {
												$mv_photo_cdns[] = 'https://'.$host.$photo_str;
											}
										}else if(array_key_exists('cover_urls', $value)){
											$mv_photo_cdns[] = $value['cover_urls'][0]['url'];
										}
										
										$mv_photo_cdns = json_encode($mv_photo_cdns);
									}
									$temp_data = array(
										'kwaiId' => $value['user_id'],
										'user_name' => $value['user_name'],
										'sex' => $value['user_sex'],
										'desc' => $value['caption'],
										'cover_thumbnail_urls' => $video_img_url,
										'mv_photo_cdns' => $mv_photo_cdns,
										'view_count' => $value['view_count'],
										'like_count' => $value['like_count'],
										'comment_count' => 0,
										'time' => $value['user_name'],
										'photo_id' => $value['photo_id'],
										'mtype' => $mtype,
										'created_at' => date('Y-m-d H:i:s')
									);
									$this->db->insert('download_video',$temp_data);
								}catch(Exception $e){
									log_message("error",$e->getMessage().'xxx'.$photo_id);
								}
								
							}else{
								//log_message("error",'pid'.$photo_id);
							}
							
						}
						
					}
				}else{

				}
				// $this->db->insert('myfollows',array(
				// 	'response' => serialize($json_array)
				// ));
			}
			
		}
	}

	private function check_photo_id_exist($photo_id){
		$sql = "SELECT COUNT(*) as `counts` FROM download_video where photo_id = ?";
		$query = $this->db->query($sql,array($photo_id));
		$result = $query->row_array();
		return $result['counts']?true:false;
	}

	/**
	* 下载个人资料的所有图片和视频
	*/
	public function profileshare(){
		$user_id =  $this->uri->segment(3, 0);
		if($user_id > 0)
		{
			$sql = "SELECT * FROM download_video where kwaiId = ?";
			$query = $this->db->query($sql,array($user_id));
			if($query->num_rows() > 0){
				$result = $query->result_array();
				$this->common_download($result);
			}
			
		}
	}

	public function sshare(){
		$photo_id =  intval($this->uri->segment(3, 0));
		log_message('info','singleshare photo_id:'.$photo_id);
		if($photo_id > 0)
		{
			$sql = "SELECT * FROM download_video where photo_id = ?";
			$query = $this->db->query($sql,array($photo_id));
			if($query->num_rows() > 0){
				$result = $query->result_array();
				$this->common_download($result);
			}
			
		}
	}

	public function common_download($result){
		foreach ($result as $key => $value) {
			$mtype = $value['mtype'];
			$mv_photo_cdns = $value['mv_photo_cdns'];
			$is_download = $value['is_download'];
			$photo_id = $value['photo_id'];
			
			if($is_download <= 0){
				 $this->download_video_or_photo($mtype,$mv_photo_cdns,$photo_id);
			}

			
		}
	}

	public function download_video_or_photo($mtype,$mv_photo_cdns,$photo_id){
		$save_path = '';

		$local_files = array();

		if($mtype == 3){
			$this->download_file($save_path,$mv_photo_cdns);
		}else if($mtype == 6){
			$save_path = $photo_id.'_'.$mtype;
			$list = json_decode($mv_photo_cdns,true);
			foreach ($list as $key => $value) {
				$this->download_file($save_path,$value);
			}
		}

	}

	public function test()
	{
		$save_path = '5212072166416693142_6';
		$url = 'https://js2.a.yximgs.com/ufile/atlas/NTk3MjQwMjE2XzIzMjMwMTQ1NDE5XzE1ODEyNjI5NzgxODQ=_0.webp';
		$this->download_file($save_path,$url);


		
	}

	public function test2(){
		$this->load->library('ftp');

		$config['hostname'] = '192.168.1.114';
		$config['username'] = 'francis';
		$config['password'] = 'francis';
		$config['debug']    = TRUE;
		$config['port'] = '2221';
		print_r($config);
		$this->ftp->connect($config);
		
		// foreach ($local_files as $key => $value) {
		// 	$file_name = basename($value);
		// 	log_message('info','filename:'.$file_name);
		// 	$this->ftp->upload($value, '/storage/emulated/0/Download/ksvideo/'.$file_name);
		// }
		
		$this->ftp->close();
	}


	public function ftp_upload_file($local_file){
		return true;
		$this->load->library('ftp');

		$config['hostname'] = '192.168.1.114';
		$config['username'] = 'francis';
		$config['password'] = 'francis';
		$config['debug']    = TRUE;
		$config['port'] = '2221';
		$this->ftp->connect($config);
		
		$file_name = basename($local_file);
		log_message('info','filename:'.$file_name);
		//$this->ftp->upload($value, '/storage/emulated/0/Download/ksvideo/'.$file_name);
		$this->ftp->upload($local_file, '/Download/ksvideo/'.$file_name);
		$this->ftp->close();
	}


	public function download_file($save_path,$url){
		$url=strtok($url,'?');
		// Inintialize directory name where 
		// file will be save 
		$dir = APPPATH.'ksfiles'.DIRECTORY_SEPARATOR.$save_path.DIRECTORY_SEPARATOR;
		if(!is_dir($dir)){
			mkdir($dir);
		}
		// Initialize a file URL to the variable 
		  
		// Initialize the cURL session 
		$ch = curl_init($url); 
		  
		
		
		// Use basename() function to return 
		// the base name of file  
		$file_name = basename($url); 
		  
		// Save file into file location 
		$save_file_loc = $dir . $file_name; 
		if(!file_exists($save_file_loc)){
			// Open file  
			$fp = fopen($save_file_loc, 'wb'); 
			  
			// It set an option for a cURL transfer 
			//curl_setopt($ch, CURLOPT_FILE, $fp); 
			curl_setopt($ch, CURLOPT_HEADER, 0);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			  
			// Perform a cURL session 
			$result = curl_exec($ch); 
			
			// Closes a cURL session and frees all resources 
			curl_close($ch); 
			fwrite($fp, $result);
			// Close file 
			fclose($fp);
			$this->ftp_upload_file($save_file_loc);
		}else{
			$this->ftp_upload_file($save_file_loc);
		}
		  
		
		//echo $file_name;
		// 返回路径
		return $save_file_loc;
	}

	public function video_is_download($photo_id){
		$sql = "UPDATE download_video set is_download=1 where photo_id = ?";
		$this->db->query($sql,array($photo_id));
	}

	public function test3(){
		$url = 'https://js2.a.yximgs.com/ufile/atlas/NTk3MjQwMjE2XzIzMjMwMTQ1NDE5XzE1ODEyNjI5NzgxODQ=_0.webp?test=1';

		//去除?后面的参数
		$url=strtok($url,'?'); 
		// 初始化文件存放的路径 
		$dir = dirname(__FILE__).DIRECTORY_SEPARATOR;
		if(!is_dir($dir)){
			mkdir($dir);
		}
		  
		// 初始化 cURL 会话
		$ch = curl_init($url); 
		  


		// 使用 basename() 方法返回值来获取文件的文件名称
		$file_name = basename($url); 
		  
		// 存放文件到本地的完整路径
		$save_file_loc = $dir . $file_name; 
		echo $save_file_loc;
		// 打开文件  
		$fp = fopen($save_file_loc, 'wb'); 
		curl_setopt($ch, CURLOPT_HEADER, 0);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		  
		// 处理一个 cURL 会话
		$result = curl_exec($ch); 

		// 关闭一个 cURL会话并释放所有资源
		curl_close($ch); 

		//将curl请求返回结果写入到 fp 打开的文件句柄中
		fwrite($fp, $result);

		// 关闭 fp 句柄并释放所有资源
		fclose($fp);
	}
}
