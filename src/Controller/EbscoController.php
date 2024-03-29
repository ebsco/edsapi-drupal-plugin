<?php

/**
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
require_once __DIR__ . "/../../lib/EBSCOAPI.php";
require_once __DIR__ . "/../../lib/EBSCOConnector.php";

header('Access-Control-Allow-Origin "*"');
/**
 * Default controller for the ebsco module.
 */
class EbscoController extends ControllerBase  {

	public function content() {
		return [];
	}

	public function advanced()
	{
			$is_xhr_results = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';

			$resultsFinal = [
					'#theme' => 'ebsco_advanced_search',
			];

			if (empty($_ebsco_document)) {
					$_ebsco_document = new \EBSCODocument();
			}

			$_ebsco_document->autocomplete();
			$autoCompleteRequest = $_ebsco_document->autocomplete();
			$result = str_replace('&amp;', '&', $autoCompleteRequest);
			$url = $autoCompleteRequest;
			$buildAutocomplete = [];
			$buildAutocomplete['#attached']['library'][] = 'ebsco/autocomplete';
			$buildAutocomplete['#attached']['drupalSettings']['autocomplete']['authenticationToken'] = $url['authenticationToken'];
			$buildAutocomplete['#attached']['drupalSettings']['autocomplete']['authenticationTimeout'] = $url['authenticationTimeout'];
			$buildAutocomplete['#attached']['drupalSettings']['autocomplete']['autocompleteUrl'] = $url['autocompleteUrl'];
			$buildAutocomplete['#attached']['drupalSettings']['autocomplete']['autocompleteToken'] = $url['autocompleteToken'];
			$buildAutocomplete['#attached']['drupalSettings']['autocomplete']['autocompleteTokenTimeOut'] = $url['autocompleteTokenTimeOut'];
			$buildAutocomplete['#attached']['drupalSettings']['autocomplete']['autocompleteCustId'] = $url['autocompleteCustId'];
			$authenticationToken = $url['authenticationToken'];
			$authenticationTimeout = $url['authenticationTimeout'];
			$autocompleteUrl = $url['autocompleteUrl'];
			$autocompleteToken = $url['autocompleteToken'];
			$autocompleteTokenTimeOut = $url['autocompleteTokenTimeOut'];
			$autocompleteCustId = $url['autocompleteCustId'];

			if (!$is_xhr_results) {
					$result = $resultsFinal + $buildAutocomplete;

					return $result;
			} else {
					return $result;
			}

			return $resultsFinal;
	}

	public function results()
    {
        $is_xhr_results = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';

        $resultsFinal = [
            '#theme' => 'ebsco_results',
        ];

        if (empty($_ebsco_document)) {
            $_ebsco_document = new \EBSCODocument();
        }

        $_ebsco_document->autocomplete();
        $autoCompleteRequest = $_ebsco_document->autocomplete();
        $result = str_replace('&amp;', '&', $autoCompleteRequest);
        $url = $autoCompleteRequest;
        $buildAutocomplete = [];
        $buildAutocomplete['#attached']['library'][] = 'ebsco/autocomplete';
        $buildAutocomplete['#attached']['drupalSettings']['autocomplete']['authenticationToken'] = $url['authenticationToken'];
        $buildAutocomplete['#attached']['drupalSettings']['autocomplete']['authenticationTimeout'] = $url['authenticationTimeout'];
        $buildAutocomplete['#attached']['drupalSettings']['autocomplete']['autocompleteUrl'] = $url['autocompleteUrl'];
        $buildAutocomplete['#attached']['drupalSettings']['autocomplete']['autocompleteToken'] = $url['autocompleteToken'];
        $buildAutocomplete['#attached']['drupalSettings']['autocomplete']['autocompleteTokenTimeOut'] = $url['autocompleteTokenTimeOut'];
        $buildAutocomplete['#attached']['drupalSettings']['autocomplete']['autocompleteCustId'] = $url['autocompleteCustId'];
        $authenticationToken = $url['authenticationToken'];
        $authenticationTimeout = $url['authenticationTimeout'];
        $autocompleteUrl = $url['autocompleteUrl'];
        $autocompleteToken = $url['autocompleteToken'];
        $autocompleteTokenTimeOut = $url['autocompleteTokenTimeOut'];
        $autocompleteCustId = $url['autocompleteCustId'];

        if (!$is_xhr_results) {
            $result = $resultsFinal + $buildAutocomplete;

            return $result;
        } else {
            return $result;
        }

        return $resultsFinal;
    }

		public function detailed_record()
    {
        $is_xhr_results = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';

        $resultsFinal = [
            '#theme' => 'ebsco_result',
        ];

        if (empty($_ebsco_document)) {
            $_ebsco_document = new \EBSCODocument();
        }

        $_ebsco_document->autocomplete();
        $autoCompleteRequest = $_ebsco_document->autocomplete();
        $result = str_replace('&amp;', '&', $autoCompleteRequest);
        $url = $autoCompleteRequest;
        $buildAutocomplete = [];
        $buildAutocomplete['#attached']['library'][] = 'ebsco/autocomplete';
        $buildAutocomplete['#attached']['drupalSettings']['autocomplete']['authenticationToken'] = $url['authenticationToken'];
        $buildAutocomplete['#attached']['drupalSettings']['autocomplete']['authenticationTimeout'] = $url['authenticationTimeout'];
        $buildAutocomplete['#attached']['drupalSettings']['autocomplete']['autocompleteUrl'] = $url['autocompleteUrl'];
        $buildAutocomplete['#attached']['drupalSettings']['autocomplete']['autocompleteToken'] = $url['autocompleteToken'];
        $buildAutocomplete['#attached']['drupalSettings']['autocomplete']['autocompleteTokenTimeOut'] = $url['autocompleteTokenTimeOut'];
        $buildAutocomplete['#attached']['drupalSettings']['autocomplete']['autocompleteCustId'] = $url['autocompleteCustId'];
        $authenticationToken = $url['authenticationToken'];
        $authenticationTimeout = $url['authenticationTimeout'];
        $autocompleteUrl = $url['autocompleteUrl'];
        $autocompleteToken = $url['autocompleteToken'];
        $autocompleteTokenTimeOut = $url['autocompleteTokenTimeOut'];
        $autocompleteCustId = $url['autocompleteCustId'];

        if (!$is_xhr_results) {
            $result = $resultsFinal + $buildAutocomplete;

            return $result;
        } else {
            return $result;
        }

        return $resultsFinal;
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

			header('Location: '.$url);
			die();
		}
		else
		{
			$_SESSION['EBSCO']['redirect'] = \Drupal::destination()->getAsArray();
			//return new RedirectResponse(\Drupal::url('user.page'));
			return new RedirectResponse( Url::fromRoute('user.page')->toString() );
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
		$_SESSION['EBSCO']['redirect'] = \Drupal::destination()->getAsArray();
		if ($is_xhr) {
			echo "<script type=\"text/javascript\">window.location.href = '" . \Drupal\Core\Url::fromRoute('user.page')->toString() . "';</script>";
			return;
		}
		else {
			$_SESSION['EBSCO']['redirect'] = \Drupal::destination()->getAsArray();
			return new RedirectResponse( Url::fromRoute('user.page')->toString() );
		}
		}
	}



	public function image_quick_view() {
		$is_xhr = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';

		if (\Drupal::currentUser()->isAuthenticated()) {
		if ($is_xhr) {
			return [
			'#theme' => 'ebsco_image_quick_view'
			];
			return true;
		}
		else {
			return [
			'#theme' => 'ebsco_image_quick_view'
			];

		}
		}
		else {
		$_SESSION['EBSCO']['redirect'] = \Drupal::destination()->getAsArray();
		if ($is_xhr) {
			echo "<script type=\"text/javascript\">window.location.href = '" . \Drupal\Core\Url::fromRoute('user.page')->toString() . "';</script>";
			return;
		}
		else {
			$_SESSION['EBSCO']['redirect'] = \Drupal::destination()->getAsArray();
			//return new RedirectResponse(\Drupal::url('user.page'));
			return new RedirectResponse( Url::fromRoute('user.page')->toString() );
		}
		}

	}


	public function exportformat_detail() {
		$is_xhr = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';

		if ($is_xhr) {
			return [
			'#theme' => 'ebsco_exportformat_detail'
			];
			return true;
		}
		else {
			return [
			'#theme' => 'ebsco_exportformat_detail'
			];

		}

	}


	public function exportformat() {
		global $_ebsco_document;
		$params = $_REQUEST;

		if (\Drupal::currentUser()->isAuthenticated()) {
			if (empty($_ebsco_document)) {
				$_ebsco_document = new \EBSCODocument();
			}
			$_ebsco_document->export();
			$record = $_ebsco_document->record();

			$url = $_ebsco_document->export();


			header('Location: '.$url);
			die();
		}
		else
		{
			$_SESSION['EBSCO']['redirect'] = \Drupal::destination()->getAsArray();
			//return new RedirectResponse(\Drupal::url('user.page'));
			return new RedirectResponse( Url::fromRoute('user.page')->toString() );
		}

	}


	public function citation_styles_detail() {
		$is_xhr = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
		if (\Drupal::currentUser()->isAuthenticated()) {
		if ($is_xhr) {
			return [
			'#theme' => 'ebsco_citation_styles_detail'
			];
			return true;
		}
		else {
			return [
			'#theme' => 'ebsco_citation_styles_detail'
			];

		}
		}
		else {
		$_SESSION['EBSCO']['redirect'] = \Drupal::destination()->getAsArray();
		if ($is_xhr) {
			echo "<script type=\"text/javascript\">window.location.href = '" . \Drupal\Core\Url::fromRoute('user.page')->toString() . "';</script>";
			return;
		}
		else {
			$_SESSION['EBSCO']['redirect'] = \Drupal::destination()->getAsArray();
			//return new RedirectResponse(\Drupal::url('user.page'));
			return new RedirectResponse( Url::fromRoute('user.page')->toString() );
		}
		}

	}


	public function citation_styles() {
		global $_ebsco_document;
		$params = $_REQUEST;

		if (\Drupal::currentUser()->isAuthenticated()) {
			if (empty($_ebsco_document)) {
				$_ebsco_document = new \EBSCODocument();
			}
			$_ebsco_document->citation();
			$record = $_ebsco_document->record();

			$url = $_ebsco_document->citation();

			header('Location: '.$url);
			die();
		}
		else
		{
			$_SESSION['EBSCO']['redirect'] = \Drupal::destination()->getAsArray();
			//return new RedirectResponse(\Drupal::url('user.page'));
			return new RedirectResponse( Url::fromRoute('user.page')->toString() );
		}

	}

	public function image_quick_url() {
    global $_ebsco_document;
    $params = $_REQUEST;

		if (\Drupal::currentUser()->isAuthenticated()) {
			if (empty($_ebsco_document)) {
				$_ebsco_document = new \EBSCODocument();
			}
			$_ebsco_document->retrieve();
			$record = $_ebsco_document->record();
			$url=str_replace('&amp;','&',$record->image_quick_url);

			// redirect on the new window
			header('Location: '.$url);
			die();
		}
		else
		{
			$_SESSION['EBSCO']['redirect'] = \Drupal::destination()->getAsArray();

			return new RedirectResponse( Url::fromRoute('user.page')->toString() );
		}

	}


	public function autocomplete() {

		global $_ebsco_document;


		$params = $_REQUEST;




			if (empty($_ebsco_document)) {
				$_ebsco_document = new \EBSCODocument();
			}


			$_ebsco_document->autocomplete();

			$autoCompleteRequest = $_ebsco_document->autocomplete();

			$result = str_replace('&amp;','&',$autoCompleteRequest);


			$url = $autoCompleteRequest;

			$buildAutocomplete = [];

			$buildAutocomplete['#attached']['library'][] = 'ebsco/autocomplete';
			$buildAutocomplete['#attached']['drupalSettings']['autocomplete']['authenticationToken'] = $url['authenticationToken'];
			$buildAutocomplete['#attached']['drupalSettings']['autocomplete']['authenticationTimeout'] = $url['authenticationTimeout'];
			$buildAutocomplete['#attached']['drupalSettings']['autocomplete']['autocompleteUrl'] = $url['autocompleteUrl'];
			$buildAutocomplete['#attached']['drupalSettings']['autocomplete']['autocompleteToken'] = $url['autocompleteToken'];
			$buildAutocomplete['#attached']['drupalSettings']['autocomplete']['autocompleteTokenTimeOut'] = $url['autocompleteTokenTimeOut'];
			$buildAutocomplete['#attached']['drupalSettings']['autocomplete']['autocompleteCustId'] = $url['autocompleteCustId'];

			$authenticationToken = $url['authenticationToken'];
			$authenticationTimeout = $url['authenticationTimeout'];
			$autocompleteUrl = $url['autocompleteUrl'];
			$autocompleteToken = $url['autocompleteToken'];
			$autocompleteTokenTimeOut = $url['autocompleteTokenTimeOut'];
			$autocompleteCustId = $url['autocompleteCustId'];

			return $buildAutocomplete;

			header('Location: '.$url);
			die();



	}


}
