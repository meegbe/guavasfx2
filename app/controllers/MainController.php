<?php

namespace App\Controllers;

use BaseController;
use File;
use Request;
use View;

class MainController extends BaseController
{

	public function __construct()
	{
		// 
	}

    public function home()
    {
        $data['test'][] = 'Test Param 1';
        $data['test'][] = 'Test Param 2';
        $data['test'][] = 'Test Param 3';
        $data['nilai']['A'] = 40;

        if(Request::isAjaxRequest()) {
            
            $files = File::request('file');
            $files->move(ROOT_DIR);
        }
        return Request::all();
        return View::render('home', $data);
    }

}