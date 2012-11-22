<?php
/*
 * DiogoBenica\FileUpload\UploadedFile
 * https://github.com/diogobenica/file-upload
 *
 * Copyright 2012, Diogo Benicá
 * http://diogobenica.com
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */
namespace DiogoBenica\FileUpload;

class UploadedFile
{
	public $tempFilename;
	public $filename;
	public $size;
	public $type;

	public __contruct($tempFilename, $filename, $size, $type)
	{
		$this->tempFilename = $tempFilename;
		$this->filename = $filename;
		$this->size = $size;
		$this->type = $type;
	}
}