<?php
/**
 * app/controllers/UsersController.php
 *
 * @author Nicolas CARPi <nicolas.carpi@curie.fr>
 * @copyright 2012 Nicolas CARPi
 * @see https://www.elabftw.net Official website
 * @license AGPL-3.0
 * @package elabftw
 */
namespace Elabftw\Elabftw;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Users infos from admin page
 */
$redirect = true;

try {
    require_once '../../app/init.inc.php';

    $FormKey = new FormKey($Session);

    $tab = 1;
    $location = '../../admin.php?tab=' . $tab;

    // (RE)GENERATE AN API KEY (from profile)
    if ($Request->request->has('generateApiKey')) {
        $Response = new JsonResponse();
        $redirect = false;
        if ($App->Users->generateApiKey()) {
            $Response->setData(array(
                'res' => true,
                'msg' => _('Saved')
            ));
        } else {
            $Response->setData(array(
                'res' => false,
                'msg' => Tools::error()
            ));
        }
        $Response->send();
    }

    // VALIDATE
    if ($Request->request->has('usersValidate')) {
        $tab = 2;
        if (!$Session->get('is_admin')) {
            throw new Exception('Non admin user tried to access admin panel.');
        }

        // loop the array
        foreach ($Request->request->get('usersValidateIdArr') as $userid) {
            $Session->getFlashBag()->add('ok', $App->Users->validate($userid));
        }
    }

    // UPDATE USERS
    if ($Request->request->has('usersUpdate')) {
        $tab = 2;
        if (!$Session->get('is_admin')) {
            throw new Exception('Non admin user tried to access admin panel.');
        }
        if ($Request->request->has('fromSysconfig')) {
            $location = "../../sysconfig.php?tab=$tab";
        } else {
            $location = "../../admin.php?tab=$tab";
        }

        if ($App->Users->update($Request->request->all())) {
            $Session->getFlashBag()->add('ok', _('Configuration updated successfully.'));
        }
    }

    // ARCHIVE USER
    if ($Request->request->has('usersArchive')) {
        if (!$Session->get('is_admin')) {
            throw new Exception('Non admin user tried to access admin panel.');
        }
        $Users = new Users($Request->request->get('userid'));
        $Response = new JsonResponse();
        $redirect = false;

        if ($Users->archive()) {
            $Response->setData(array(
                'res' => true,
                'msg' => _('Saved')
            ));
        } else {
            $Response->setData(array(
                'res' => false,
                'msg' => Tools::error()
            ));
        }
        $Response->send();
    }


    // DESTROY
    if ($FormKey->validate($Request->request->get('formkey'))
        && $Request->request->has('usersDestroy')) {

        $tab = 2;
        if (!$Session->get('is_admin')) {
            throw new Exception('Non admin user tried to access admin panel.');
        }

        if ($App->Users->destroy(
            $Request->request->get('usersDestroyEmail'),
            $Request->request->get('usersDestroyPassword')
        )) {
            $Session->getFlashBag()->add('ok', _('Everything was purged successfully.'));
        }
    }

} catch (Exception $e) {
    $App->Logs->create('Error', $Session->get('userid'), $e->getMessage());
    $Session->getFlashBag()->add('ko', Tools::error());

} finally {
    if ($redirect) {
        header("Location: $location");
    }
}
