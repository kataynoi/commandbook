<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;
    protected $currentUser = null;
    protected $currentUserRoles = null;
    protected $db; // <-- 1. เพิ่ม property นี้

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['form', 'url', 'MyDateHelper'];
    

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        

        parent::initController($request, $response, $logger);
        $this->db = \Config\Database::connect();
        if (session()->get('isLoggedIn')) {
            $this->currentUserRoles = session()->get('roles') ?? [];
            $this->currentUser = [
                'id'            => session()->get('user_id'),
                'username'      => session()->get('username'),
                'changwatcode'  => session()->get('changwatcode'),  // <-- เพิ่ม
                'ampurcodefull' => session()->get('ampurcodefull'),
                'hospcode' => session()->get('hospcode'),
                'villagecodefull' => session()->get('villagecode'),// <-- แก้ไข
            ];
        }
        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = service('session');
    }
}
