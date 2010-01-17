<?php
/*
	MadMailer => a short, sweet PHP class for the MadMimi API.
	"Mailing list management made easy."
	(Many thanks to Dave, Gary, and the rest of the MadMimi crew for creating such an awesome API!)
	
	For release notes, see the README that should have been included.
	
	_______________________________________
	
	The MIT License

	Copyright (c) 2009 Nicholas Young

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.
*/

class MadMailer {
	function __construct($username, $api_key, $debug = false, $print_tx_id = false) {
		$this->username = $username;
		$this->api_key = $api_key;
		$this->debug = $debug;
		$this->print_tx_id = $print_tx_id;
		$this->mailer_url = "https://madmimi.com/mailer";
		$this->new_lists_url = "http://madmimi.com/audience_lists";
		$this->audience_members_url = "http://madmimi.com/audience_members";
		$this->lists_url = "http://madmimi.com/audience_lists/lists.xml?username=%username%&api_key=%api_key%";
		$this->memberships_url = "http://madmimi.com/audience_members/%email%/lists.xml?username=%username%&api_key=%api_key%";
	}
	function DoRequest($url, $method = 'GET', $return = false, $mail = false, $post_arr = null) {
		ob_start();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if ($method == 'POST' && $post_arr != null) {
			curl_setopt($ch, CURLOPT_POST, TRUE);
			if ($mail == false) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $this->build_postfields($post_arr));
			} else {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $this->build_request($post_arr['recipient'], $post_arr['message'], $post_arr['body']));
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			}
		}
		if ($this->print_tx_id == false) {
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		} else {
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
		}
		if ($this->debug == false) {
			$result = curl_exec($ch) or die(curl_error($ch));
			if (!$result) {
				print "Error: " . $result;
			}
		}
		curl_close($ch);
		if ($return == true) {
			return $result;
		}
		if ($method == 'POST' && $post_arr != null && $mail == true) {
			if ($this->print_tx_id == true) {
				return $result;
			}
		}
		ob_flush();
	}
	function prepare_url($url, $email = null) {
		$url = str_replace('%username%', $this->username, $url);
		$url = str_replace('%api_key%', $this->api_key, $url);
		if ($email != null) {
			$url = str_replace('%email%', $email, $url);
		}
		return $url;
	}
	function build_postfields($arr) {
		$post_string = "username=$this->username&api_key=$this->api_key&";
		foreach($arr as $key => $value) {
			$post_string .= "" . $key . "=" . urlencode($value) . "&";
		}
		$post_string = substr($post_string, 0, -1);
		return $post_string;
	}
	function construct_body($body_data) {
		foreach ($body_data as $key => $value) {
			$body_string .= $key . ': ' . urlencode($value) . "\n";
		}
		return $body_string;
	}
	function build_request($recipient_arr, $message_arr, $body_arr) {
		$request_string = "username=$this->username";
		$request_string .= "&api_key=$this->api_key";
		$request_string .= "&promotion_name=" . $message_arr['PromoName'];
		$request_string .= "&recipients=" . $recipient_arr['Name'];
		$request_string .= " <" . $recipient_arr['Email'] . ">";
		$request_string .= "&subject=" . $message_arr['Subject'];
		$request_string .= "&from=" . $message_arr['FromAddr'];
		if ($body_arr['raw_html']) {
			$request_string .= "&raw_html=" . urlencode($body_arr['raw_html']);
		} else {
			$request_string .= "&body=--- " . $this->construct_body($body_arr);
		}
		if ($this->debug == true) {
			header("Content-type: text");
			print $request_string;
		} else {
			return $request_string;
		}
	}
	function build_csv($user, $list = null) {
		if ($list != null) {
			$csv = "name,email,list\n";
			$csv .= $user['Name'] . "," . $user['Email'] . "," . (int)$list . "\n";
		} else {
			$csv = "name,email\n";
			$csv .= $user['Name'] . "," . $user['Email'] . "\n";
		}
		return $csv;
	}
	function Lists() {
		$url = $this->prepare_url($this->lists_url);
		$result = $this->DoRequest($url, 'GET', true);
		$lists = new SimpleXMLElement($result);
		return $lists;
	}
	function Memberships($email) {
		$url = $this->prepare_url($this->memberships_url, $email);
		$result = $this->DoRequest($url, 'GET', true);
		$lists = new SimpleXMLElement($result);
		return $lists;
	}
	function NewList($list_name) {
		$arr = array('name' => $list_name);
		$result = $this->DoRequest($this->new_lists_url, 'POST', true, false, $arr);
		return $result;
	}
	function DeleteList($list_name) {
		$arr = array('_method' => 'delete');
		$result = $this->DoRequest($this->new_lists_url . "/" . rawurlencode($list_name), 'POST', true, false, $arr);
	}
	function AddUser($user, $list_name = null) {
		$csv = $this->build_csv($user);
		$arr = array('username' => $this->username, 'api_key' => $this->api_key, 'email' => $user['email']);
		$this->DoRequest($this->new_lists_url . "/" . rawurlencode($list_name) . "/add", 'POST', false, false, $arr);
	}
	function RemoveUser($user, $list_name) {
		$arr = array('username' => $this->username, 'api_key' => $this->api_key, 'email' => $user['email']);
		$this->DoRequest($this->new_lists_url . "/" . rawurlencode($list_name) . "/remove", 'POST', false, false, $arr);
	}
	function SendMessage($recipient_array, $message_array, $body_array) {
		$arr = array();
		$arr['recipient'] = $recipient_array;
		$arr['message'] = $message_array;
		$arr['body'] = $body_array;
		$this->DoRequest($this->mailer_url, 'POST', false, true, $arr);
	}
}
?>