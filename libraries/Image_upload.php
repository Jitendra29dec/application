<?php

class Image_Upload {
	private $CI;
	private $field = 'image';
	private $allowed_types = "gif|jpg|png|jpeg";
	private $uploadpath = "assets/images/";
	private $thumbpath = '';
	private $thumbdim = array("h"=>100,"w"=>100);
	
	
	
	public function __construct($config = array()){

		$this->initialize($config);
		$this->CI =& get_instance();
	}
	
	public function initialize($config){
		$this->thumbpath = $this->uploadpath.'thumb';
		if(count($config) > 0){
			foreach($config as $key=>$val){
				$this->{$key} = $val;	
			}
		}		
	}
	
	/*
	 * Uploading files
	 */
	
	public function uploadfile(){
			
			$config["upload_path"] = $this->uploadpath;
			$config["allowed_types"] = $this->allowed_types;
			$this->CI->load->library("upload");
			$this->CI->upload->initialize($config);
			if(!$this->CI->upload->do_upload($this->field)){ 
		       	$error = $this->CI->upload->display_errors("<div class='text-danger'>","</div>");
		       	return array("status"=> false,"error" => $error);
		    }
		    else {
		      
		     	$data_upload_files =$this->CI->upload->data();
				if($this->thumbpath){
                    $this->resize($data_upload_files["full_path"]);
				}
	        	return array("status"=>true,"filedata"=>$data_upload_files);
			}	
	}

    public function resize($imagepath){
        $config_thumb = array(
            "image_library" => "gd2",
            "source_image" => $imagepath,
            "new_image" => $this->thumbpath,
            "maintain_ratio" => TRUE,
            "create_thumb" => TRUE,
            "thumb_marker" => "",
            "width" => $this->thumbdim['w'],
            "height" => $this->thumbdim['h']
        );

        // initializing
        $this->CI->load->library("image_lib", $config_thumb);
        $this->CI->image_lib->initialize($config_thumb);
        if (!$this->CI->image_lib->resize()) {
            $error = $this->CI->image_lib->display_errors("<div class='text-danger'> Thumb Error: ", "</div>");
            return array("status"=> false,"error" => $error);
        }
    }

    /**
     * @param string $thumbpath
     * @return class instance
     */
    public function setThumbpath($thumbpath)
    {
        $this->thumbpath = $thumbpath;
        return $this;
    }

    /**
     * @param array $thumbdim
     * @return class instance
     */
    public function setThumbdim($thumbdim)
    {
        if(!isset($thumbdim['w']) || !isset($thumbdim['h'])) return;
        $this->thumbdim = $thumbdim;
        return $this;
    }

    function reArrayFiles($file_post) {
        $file_ary = array();
        $file_count = count($file_post['name']);
        $file_keys = array_keys($file_post);

        for ($i=0; $i<$file_count; $i++) {
            foreach ($file_keys as $key) {
                $file_ary[$i][$key] = $file_post[$key][$i];
            }
        }

        return $file_ary;
    }
}