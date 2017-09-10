<?php

namespace Spirit\Request;

class FileVariables extends Variables
{
    /**
     * @var UploadedFile[]
     */
    protected $data;

    /**
     * HeaderVariables constructor.
     * @param array $data is $_FILES
     */
    public function __construct(array $data = [])
    {
        $new_data = [];
        foreach($data as $key => $value) {
            if (is_array($value['name'])) {
                $__files = [];
                foreach($value['name'] as $k => $v) {

                    $__file = [
                        'name' => $value['name'][$k],
                        'size' => $value['size'][$k],
                        'type' => $value['type'][$k],
                        'tmp_name' => $value['tmp_name'][$k],
                        'error' => $value['error'][$k],
                    ];
                    $__files[] = UploadedFile::make($__file);
                }

                $result[$key] = $__files;
            } else {
                $new_data[$key] = UploadedFile::make($value);
            }
        }

        parent::__construct($new_data);
    }

}