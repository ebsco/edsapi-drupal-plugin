<?php /**
 * @file
 * Contains \Drupal\ebsco\Controller\EbscoController.
 */

namespace Drupal\ebsco\Controller;

use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

require_once __DIR__ . "/../../lib/EBSCODocument.php";
			
/**
 * Default controller for the ebsco module.
 */
class EbscoController extends ControllerBase  {
	public function content() {
		return [
		   '#theme' => 'ebsco_basic_search',
		];
	}
	
	public function advanced() {
		return [
		  '#theme' => 'ebsco_advanced_search'
		];
	}
	
	public function results() {
		return [
		  '#theme' => 'ebsco_results'
		];
	}

  
  public function detailed_record() {
    $is_xhr = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    if ($is_xhr) {
		return [
		  '#theme' => 'ebsco_result'
		];
		 return true;
    }
    else {
      // @FIXME
		return [
		  '#theme' => 'ebsco_result'
		];

    }
  }


  public function pdf_page() {
    global $_ebsco_document;
    $params = $_REQUEST;

    if (\Drupal::currentUser()->isAuthenticated()) {
		if (empty($_ebsco_document)) {
			$_ebsco_document = new \EBSCODocument();
		}
		$_ebsco_document->retrieve();
		$record = $_ebsco_document->record();
		$url=str_replace('&amp;','&',$record->pdf_link);
		// redirect on the new window
		header('Location: '.$url);
		die();
    }
    else 
	{
		$_SESSION['EBSCO']['redirect'] = drupal_get_destination();
		return new RedirectResponse(\Drupal::url('user.page'));
    }

  }

  public function fulltext_page() {
    $is_xhr = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    if (\Drupal::currentUser()->isAuthenticated()) {
      if ($is_xhr) {
		return [
		  '#theme' => 'ebsco_result'
		];
		return true;
      }
      else {
		return [
		  '#theme' => 'ebsco_result'
		];

      }
    }
    else {
      $_SESSION['EBSCO']['redirect'] = drupal_get_destination();
      if ($is_xhr) {
        echo "<script type=\"text/javascript\">window.location.href = '" . \Drupal\Core\Url::fromRoute('user.page')->toString() . "';</script>";
        return;
      }
      else {
		$_SESSION['EBSCO']['redirect'] = drupal_get_destination();
		return new RedirectResponse(\Drupal::url('user.page'));		
      }
    }
  }


	
}
