<?php
use PHPMailer\PHPMailer\PHPMailer;

class Message
{
    // @var to string
    // @access private
    private $to;

	// @var toName string
    // @access private
    private $toName;

    // @var from string
    // @access private
    private $from;

	// @var fromName string
    // @access private
    private $fromName;

    // @var from string
    // @access private
    private $cc;

    // @var from string
    // @access private
    private $bcc;

    // @var subject string
    // @access private
    private $subject;

    // @var body string
    // @access private
    private $body;

    // @var attachments array
    // @access public
    public $attachments = array();

	// @var attachmentsNames array
	// @access public
	public $attachmentsNames = array();

	// @var ErrorInfo string
	// @access public
	public $ErrorInfo = false;

	// @var forceSendAsSMTP boolean
	// @access public
	public static $forceSendAsSMTP = false;

	// @var emailApiConnectionError boolean
	// @access public
	public $emailApiConnectionError = false;

	// @var cids array
	// @access private
	private $cids = array();

    /*
    * METHODS
    */

    function getTo()            { return $this->to; }

	function getCc()            { return $this->cc; }

	function getBcc()           { return $this->bcc; }

    function getFrom() 			{ return $this->from; }

	function getFromName()      { return $this->fromName; }

    function getSubject()       { return $this->subject; }

    function getBody()          { return $this->body; }

	function getAllRecipientAddresses()
	{
		$email_to = str_replace(array(" ",","), array("",";"), $this->getTo());
		$email_cc = str_replace(array(" ",","), array("",";"), $this->getCc());
		$email_bcc = str_replace(array(" ",","), array("",";"), $this->getBcc());
		$email_recipient_string = $email_to.";".$email_cc.";".$email_bcc;
		$email_recipient_array = array();
		foreach (explode(";", $email_recipient_string) as $this_email) {
			if ($this_email == "") continue;
			$email_recipient_array[] = $this_email;
		}
		return $email_recipient_array;
	}

    function setTo($val)        { $this->to = $val; }

    function setCc($val)       	{ $this->cc = $val; }

    function setBcc($val)       { $this->bcc = $val; }

    function setFrom($val)      { $this->from = $val; }

	function setFromName($val) 	{ $this->fromName = $val; }

    function setSubject($val)   { $this->subject = $val; }

	/**
	 * Attaches a file
	 * @param string $file_full_path The full file path of a file (including its file name)
	 */
    function setAttachment($file_full_path, $filename="")
	{
		if (!empty($file_full_path)) {
			if ($filename == "") {
				$filename = basename($file_full_path);
			}
			$this->attachments[] = $file_full_path;
			$this->attachmentsNames[] = $filename;
		}
	}

    function getAttachments()
	{
    	return $this->attachments;
    }

    function getAttachmentsWithNames()
	{
		$attachmentsNames = array();
		$attachments = $this->getAttachments();
		if (!empty($attachments)) {
			foreach ($attachments as $attachment_key=>$this_attachment_path) {
				$attachmentName = $this->attachmentsNames[$attachment_key];
				// If another attachment has the same name, then rename it on the fly to prevent conflict
				if (isset($attachmentsNames[$attachmentName])) {
					// Prepend file name with timestamp and random alphanum to ensure uniqueness
					$attachmentName = date("YmdHis")."_".substr(md5(rand()), 0, 4)."_".$attachmentName;
				}
				$attachmentsNames[$attachmentName] = $this_attachment_path;
			}
		}
		return $attachmentsNames;
	}

	/**
	 * Sets the content of this HTML email.
	 * @param string $val the HTML that makes up the email.
	 * @param boolean $onlyBody true if the $html parameter only contains the message body. If so,
	 * then html/body tags will be automatically added, and the message will be prepended with the
	 * standard REDCap notice.
	 */
    function setBody($val, $onlyBody=false) {
		global $lang;		
		// If want to use the "automatically sent from REDCap" message embedded in HTML
		if ($onlyBody) {
			$val =
				"<html>\r\n" .
				"<body style=\"font-family:arial,helvetica;font-size:10pt;\">\r\n" .
				$lang['global_21'] . "<br /><br />\r\n" .
				$val .
				"</body>\r\n" .
				"</html>";
		}
		// For compatibility purposes, make sure all line breaks are \r\n (not just \n) 
		// and that there are no bare line feeds (i.e., for a space onto a blank line)
		$val = str_replace(array("\r\n", "\r", "\n", "\r\n\r\n"), array("\n", "\n", "\r\n", "\r\n \r\n"), $val);
		// Set body for email message
		$this->body = $val;
	}

	// Format email body for text/plain: Replace HTML link with "LINKTEXT (URL)" and fix tabs and line breaks
	public function formatPlainTextBody($body)
	{
		$plainText = $body;
		if (preg_match_all("/<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>/siU", $plainText, $matches)) {
			foreach ($matches[0] as $key=>$this_match) {
				$plainText = str_replace($this_match, $matches[3][$key]." (".$matches[2][$key].")", $plainText);
			}
		}
		$plainText = trim(str_replace(array("\r", "\n"), array("", ""), $plainText));
		$plainText = strip_tags(br2nl($plainText));
		$plainText = preg_replace("/\n\t+/", "\n", $plainText);
		$plainText = trim(preg_replace("/\t+/", " ", $plainText));
		return $plainText;
	}

    // Send the email
    public function send($removeDisplayName=false, $recipientIsSurveyParticipant=null)
	{
		// Have email Display Names been disabled at the system level?
		global $use_email_display_name;
		if (isset($use_email_display_name) && $use_email_display_name == '0') {
			$removeDisplayName = true;
		}

		// Reset flag
		$this->emailApiConnectionError = false;

		// Call the email hook
		$sendEmail = Hooks::call('redcap_email', array($this->getTo(), $this->getFrom(), $this->getSubject(), $this->getBody(), $this->getCc(),
								$this->getBcc(), $this->getFromName(), $this->getAttachmentsWithNames()));

		if (!$sendEmail) {
			// If the hook returned FALSE, then exit here without sending the email through normal methods below
			return true; // Return TRUE to note that the email was sent successfully because FALSE would imply some sort of error
		}

		// Get the Universal FROM Email address (if being used)
		$from_email = System::getUniversalFromAddess();

		// Suppress Universal FROM Address? (based on the sender's address domain)
		if (System::suppressUniversalFromAddress($this->getFrom())) {
			$from_email = ''; // Set Universal FROM address as blank so that it is ignored for this outgoing email
		}

		// Using the Universal FROM email?
		$usingUniversalFrom = ($from_email != '');

		// Set the From email for this message
		$this_from_email = (!$usingUniversalFrom ? $this->getFrom() : $from_email);

		// If the FROM email address is not valid, then return false
		if (!isEmail($this_from_email)) return false;

		if ($this->getFromName() == '') {
			// If no Display Name, then use the Sender address as the Display Name if using Universal FROM address
			$fromDisplayName = $usingUniversalFrom ? $this->getFrom() : "";
			$replyToDisplayName = '';
		} else {
			// If has a Display Name, then use the Sender address+real Display Name if using Universal FROM address
			$fromDisplayName = $usingUniversalFrom ? $this->getFromName()." <".$this->getFrom().">" : $this->getFromName();
			$replyToDisplayName = $this->getFromName();
		}
		// Remove the display name(s), if applicable
		if ($removeDisplayName) {
			$fromDisplayName = $replyToDisplayName = '';
		}

		// Replace any <img> with src="..." with "cid:" reference to attachment
		$this->cids = array();
		if (strpos($this->getBody(), "DataEntry/image_view.php") !== false || strpos($this->getBody(), "&__passthru=".urlencode("DataEntry/image_view.php")) !== false)
		{
			preg_match_all('/(src|rc-src-replace)=[\"\'](.+?)[\"\'].*?/i', $this->getBody(), $result);
			foreach ($result[2] as $key=>$img_src) {
				// Parse the URL and validate its components
				$redirectUrlParts = parse_url($img_src);
				parse_str($redirectUrlParts['query'], $urlPart);
				if (!(isset($urlPart['id']) && isset($urlPart['pid']) && isinteger($urlPart['id'])
						&& isset($urlPart['doc_id_hash']) && isinteger($urlPart['pid']))) {
					continue;
				}
				$edoc = $urlPart['id'];
				$doc_id_hash = $urlPart['doc_id_hash'];
				// Get project-level SALT using PID
				$pid = $urlPart['pid'];
				$pidSalt = Project::getProjectSalt($pid);
				// Validate doc id hash
				if ($doc_id_hash != Files::docIdHash($edoc, $pidSalt)) continue;
				$isImgFile = ($result[1][$key] == 'src');
				// Obtain the file's name and contents
				list ($mimeType, $docName, $fileContent) = Files::getEdocContentsAttributes($edoc);
				// Save file to TEMP to handle non-local storage types
				$filename_pre = APP_PATH_TEMP . date('YmdHis') . "_email_inline_img_" . substr(sha1(rand()), 0, 10);
				$filename = $filename_pre . getFileExt($docName, true);
				file_put_contents($filename, $fileContent);
				// Add attachments to array
				$this->setAttachment($filename);
				// Replace img SRC in message with CID
				$cid = basename($filename_pre);
				// Add to array to reference later when adding the attachment
				$this->cids[$filename] = $cid;
				// If inline image, add CID for src. If not an image, add as plain text filename label + add as regular attachment.
				$this->setBody(str_replace($img_src, ($isImgFile ? "cid:$cid" : ""), $this->getBody()));
			}
		}

		// Replace any file download relative links with valid full links
		$this->replaceFileDownloadLinks($recipientIsSurveyParticipant);

		## SENDGRID ONLY
		if (!empty($GLOBALS["sendgrid_api_key"]) && !self::$forceSendAsSMTP)
		{
			try {
				$email = new \SendGrid\Mail\Mail();
				$email->setFrom($this_from_email, $fromDisplayName);
				$email->setReplyTo($this->getFrom(), $this->getFromName());
				$email->setSubject($this->getSubject());
				$email->addContent("text/plain", $this->formatPlainTextBody($this->getBody()));
				$email->addContent("text/html", $this->getBody());
				foreach (preg_split("/[;,]+/", $this->getTo()) as $thisTo) {
					$thisTo = trim($thisTo);
					if ($thisTo == '') continue;
					$email->addTo($thisTo);
				}
				if ($this->getCc() != "") {
					foreach (preg_split("/[;,]+/", $this->getCc()) as $thisCc) {
						$thisCc = trim($thisCc);
						if ($thisCc == '') continue;
						$email->addCc($thisCc);
					}
				}
				if ($this->getBcc() != "") {
					foreach (preg_split("/[;,]+/", $this->getBcc()) as $thisBcc) {
						$thisBcc = trim($thisBcc);
						if ($thisBcc == '') continue;
						$email->addBcc($thisBcc);
					}
				}
				// Attachments, if any
				$attachments = $this->getAttachmentsWithNames();
				if (!empty($attachments)) {
					foreach ($attachments as $attachmentName=>$this_attachment_path) {
						$mime_type = \ExternalModules\ExternalModules::getContentType(str_replace(".", "", SendIt::getFileExtension(basename($this_attachment_path))));
						if (empty($mime_type)) $mime_type = "application/octet-stream";
						$cid = isset($this->cids[$this_attachment_path]) ? $this->cids[$this_attachment_path] : null;
						$disposition = isset($this->cids[$this_attachment_path]) ? 'inline' : null;
						$email->addAttachment(base64_encode(file_get_contents($this_attachment_path)), $mime_type, $attachmentName, $disposition, $cid);
					}
				}

				$sendgrid = new \SendGrid($GLOBALS["sendgrid_api_key"]);
				$response = $sendgrid->send($email);
				$json_response = json_decode($response->body(), true);
				if ($response->statusCode() >= 429) $this->emailApiConnectionError = true;
				if (is_array($json_response) && isset($json_response['errors'])) {
					if (isDev()) {
						print_array($response);
					}
					error_log("Email: Failed send ".print_r($json_response['errors'], true));
					$this->ErrorInfo = $json_response['errors'][0]['message'];
					return false;
				}
				$this->logSuccessfulSend('sendgrid');
				return true;
			} catch (Exception $e) {
				// echo 'Caught exception: ' . $e->getMessage() . "\n";
				error_log("Email: Failed send ".$e->getMessage());
				$this->ErrorInfo = $e->getMessage();
				return false;
			}
		}

		## MANDRILL API ONLY (does not appear to support attachments)
		if (!empty($GLOBALS["mandrill_api_key"]) && !self::$forceSendAsSMTP)
		{
			$messageData = [
					"to" => [],
					"from_email" => $this_from_email,
					"from_name" => $fromDisplayName,
					"headers" => ["Reply-To" => $this->getFrom()],
					"subject" => $this->getSubject(),
					"text" => $this->formatPlainTextBody($this->getBody()),
					"html" => $this->getBody()
			];
			foreach (preg_split("/[;,]+/", $this->getTo()) as $thisTo) {
				$thisTo = trim($thisTo);
				if ($thisTo == '') continue;
				$messageData["to"][] = ["email" => $thisTo,"type" => "to"];
			}
			if ($this->getCc() != "") {
				foreach (preg_split("/[;,]+/", $this->getCc()) as $thisCc) {
					$thisCc = trim($thisCc);
					if ($thisCc == '') continue;
					$messageData["to"][] = ["email" => $thisCc,"type" => "cc"];
				}
			}
			if ($this->getBcc() != "") {
				foreach (preg_split("/[;,]+/", $this->getBcc()) as $thisBcc) {
					$thisBcc = trim($thisBcc);
					if ($thisBcc == '') continue;
					$messageData["to"][] = ["email" => $thisBcc,"type" => "bcc"];
				}
			}
			// Attachments, if any
			$attachments = $this->getAttachmentsWithNames();
			if (!empty($attachments)) {
				$messageData["attachments"] = [];
				foreach ($attachments as $attachmentName=>$this_attachment_path) {
					$mime_type = \ExternalModules\ExternalModules::getContentType(str_replace(".", "", SendIt::getFileExtension(basename($this_attachment_path))));
					if (empty($mime_type)) $mime_type = "application/octet-stream";
					// How to add CID attachments? Does not seem to be supported by Mandrill.
					$messageData["attachments"][] = ["type"=>$mime_type, "name"=>$attachmentName, "content"=>file_get_contents($this_attachment_path)];
				}
			}
			$data = [
				"message" => $messageData
			];
			$output = self::sendMandrillRequest($data,"messages/send.json");
			if (empty($output)) {
				error_log("Email: Failed send - Unknown reason (Mandrill not available?)");
			}
			$decodedOutput = json_decode($output, true);
			## Check for error message and log if needed
			if ($decodedOutput["status"] == "error") {
				if ($decodedOutput["name"] != "GeneralError") $this->emailApiConnectionError = true;
				error_log("Email: Failed send ".$decodedOutput["message"]);
				$this->ErrorInfo = $decodedOutput["message"];
				return false;
			}
			if ($decodedOutput[0]["status"] == "rejected") {
				if ($decodedOutput[0]["name"] != "GeneralError") $this->emailApiConnectionError = true;
				error_log("Email: Failed send from ".$this_from_email." rejected because ".$decodedOutput[0]["reject_reason"]);
				$this->ErrorInfo = $output;
				return false;
			}
			$this->logSuccessfulSend('mandrill');
			return true;
		}

		## GOOGLE APP ENGINE ONLY
		if (isset($_SERVER['APPLICATION_ID']))
		{
			try
			{
				// Set up email params
				$message = new \google\appengine\api\mail\Message();
				$message->setSender($this_from_email);
				$message->setReplyTo($this->getFrom());
				$message->addTo($this->getTo());
				if ($this->getCc() != "") {
					$message->addCc($this->getCc());
				}
				if ($this->getBcc() != "") {
					$message->addBcc($this->getBcc());
				}
				$message->setSubject($this->getSubject());
				$message->setHtmlBody($this->getBody());
				// Attachments, if any
				$attachments = $this->getAttachmentsWithNames();
				if (!empty($attachments)) {
					foreach ($attachments as $attachmentName=>$this_attachment_path) {
						$cid = isset($this->cids[$this_attachment_path]) ? $this->cids[$this_attachment_path] : sha1(rand());
						$message->addAttachment($attachmentName, file_get_contents($this_attachment_path), "<".$cid.">");
					}
				}
				// Send email
				try {
					$message->send();
					$this->logSuccessfulSend();
				} catch (InvalidArgumentException $e) { }
				return true;
			}
			catch (InvalidArgumentException $e)
			{
				print "<br><b>ERROR: ".$e->getMessage()."</b>";
				return false;
			}
		}

        ## MAILGUN ONLY
        if (!empty($GLOBALS["mailgun_api_key"]) && !empty($GLOBALS["mailgun_domain_name"]) && !self::$forceSendAsSMTP) {
            try {
                $messageData = [
                    "subject" => $this->getSubject(),
                    "text" => $this->formatPlainTextBody($this->getBody()),
                    "html" => $this->getBody()
                ];
                $toAddress = [];
                foreach (preg_split("/[;,]+/", $this->getTo()) as $thisTo) {
                    $thisTo = trim($thisTo);
                    if ($thisTo == '') continue;
                    $toAddress[] = $thisTo;
                }
                $messageData['to'] = $toAddress;

                if ($this->getCc() != "") {
                    $ccAddress = [];
                    foreach (preg_split("/[;,]+/", $this->getCc()) as $thisCc) {
                        $thisCc = trim($thisCc);
                        if ($thisCc == '') continue;
                        $ccAddress[] = $thisCc;
                    }
                    $messageData['cc'] = $ccAddress;
                }

                if ($this->getBcc() != "") {
                    $bccAddress = [];
                    foreach (preg_split("/[;,]+/", $this->getBcc()) as $thisBcc) {
                        $thisCc = trim($thisBcc);
                        if ($thisBcc == '') continue;
                        $bccAddress[] = $thisBcc;
                    }
                    $messageData['bcc'] = $bccAddress;
                }
                if ($this_from_email != "") {
                    if ($fromDisplayName != "") {
                        $messageData['from'] = $fromDisplayName.' <'.$this_from_email.'>';
                    } else {
                        $messageData['from'] = $this_from_email;
                    }
                }
                // Attachments, if any
                $attachments = $this->getAttachmentsWithNames();
                if (!empty($attachments)) {
                    $messageData["attachment"] = [];
                    foreach ($attachments as $attachmentName => $this_attachment_path) {
                        $messageData["attachment"][] = ["filename" => $attachmentName, "filePath" => $this_attachment_path];
                    }
                }
                $mg = \Mailgun\Mailgun::create($GLOBALS["mailgun_api_key"]);

                # Make the call to the client.
                $response = $mg->messages()->send($GLOBALS["mailgun_domain_name"], $messageData);
                if ($response->getId() != "" && $response->getMessage() != "") {
                    $this->logSuccessfulSend('mailgun');
                    return true;
                } else {
                    error_log("Email: Failed send", true);
                    $this->ErrorInfo = "Email: Failed send";
                    return false;
                }
            } catch (Exception $e) {
                error_log("Email: Failed send ".$e->getMessage());
                $this->ErrorInfo = $e->getMessage();
                return false;
            }
        }
		## NORMAL ENVIRONMENT (using PHPMailer)
		// Init
		$mail = new PHPMailer;
		$mail->CharSet = 'UTF-8';
		// Subject and body
		$mail->Subject = $this->getSubject();
		$mail->msgHTML($this->getBody());
		// Format email body for text/plain: Replace HTML link with "LINKTEXT (URL)" and fix tabs and line breaks
		$mail->AltBody = $this->formatPlainTextBody($this->getBody());
		// From, Reply-To, and Return-Path. Also, set Display Name if possible.
		// From/Sender and Reply-To
		$mail->setFrom($this_from_email, $fromDisplayName, false);
		$mail->addReplyTo($this->getFrom(), $replyToDisplayName);
		$mail->Sender = $this_from_email; // Return-Path; This also represents the -f header in mail().
		// To, CC, and BCC
		foreach (preg_split("/[;,]+/", $this->getTo()) as $thisTo) {
			$thisTo = trim($thisTo);
			if ($thisTo == '') continue;
			$mail->addAddress($thisTo);
		}
		if ($this->getCc() != "") {
			foreach (preg_split("/[;,]+/", $this->getCc()) as $thisCc) {
				$thisCc = trim($thisCc);
				if ($thisCc == '') continue;
				$mail->addCC($thisCc);
			}
		}
		if ($this->getBcc() != "") {
			foreach (preg_split("/[;,]+/", $this->getBcc()) as $thisBcc) {
				$thisBcc = trim($thisBcc);
				if ($thisBcc == '') continue;
				$mail->addBCC($thisBcc);
			}
		}
		// Attachments
		$attachments = $this->getAttachmentsWithNames();
		if (!empty($attachments)) {
			foreach ($attachments as $attachmentName=>$this_attachment_path) {
				$cid = isset($this->cids[$this_attachment_path]) ? $this->cids[$this_attachment_path] : null;
				if ($cid == null) {
					$mail->addAttachment($this_attachment_path, $attachmentName);
				} else {
					$mail->addAttachment($this_attachment_path, $cid, PHPMailer::ENCODING_BASE64, '', 'inline');
				}
			}
		}

		/*
		// Use DKIM?
		$dkim = new DKIM();
		if ($dkim->isEnabled())
		{
			$mail->DKIM_domain = $dkim->DKIM_domain;
			$mail->DKIM_private_string = $dkim->privateKey;
			$mail->DKIM_selector = $dkim->DKIM_selector;
			$mail->DKIM_passphrase = $dkim->DKIM_passphrase;
			$mail->DKIM_copyHeaderFields = false;
			// $mail->DKIM_extraHeaders = ['List-Unsubscribe', 'List-Help'];
			// $mail->DKIM_identity = $mail->From;
		}
		*/

		// Send it
		$sentSuccessfully = $mail->send();
		// Add error message, if failed to send
		if (!$sentSuccessfully) {
			$this->ErrorInfo = $mail->ErrorInfo;
		} else {
			$this->logSuccessfulSend();
		}
		// Return boolean for success/fail
		return $sentSuccessfully;
    }

	/**
	 * Increment the emails sent counter in the database
	 */
	private function logSuccessfulSend($type='smtp')
	{
		$sql = "insert into redcap_outgoing_email_counts (`date`, $type) values ('".TODAY."', 1)
				on duplicate key update send_count = send_count+1, $type = $type+1";
		if (!db_query($sql)) return false;
		// Also delete any file attachments that were generated in the temp directory
		foreach (array_keys($this->cids) as $tempFilePath)
		{
			if (file_exists($tempFilePath)) {
				unlink($tempFilePath);
			}
		}
		// Return true on success
		return true;
	}

	/**
	 * Returns HTML suitable for displaying to the user if an email fails to send.
	 */
	function getSendError()
	{
		global $lang;
		return  "<div style='font-size:12px;background-color:#F5F5F5;border:1px solid #C0C0C0;padding:10px;'>
			<div style='font-weight:bold;border-bottom:1px solid #aaaaaa;color:#800000;'>
			<img src='".APP_PATH_IMAGES."exclamation.png'>
			{$lang['control_center_243']}
			</div><br>
			{$lang['global_37']} <span style='color:#666;'>{$this->fromName} &#60;{$this->from}&#62;</span><br>
			{$lang['global_38']} <span style='color:#666;'>{$this->toName} &#60;{$this->to}&#62;</span><br>
			{$lang['control_center_28']} <span style='color:#666;'>{$this->subject}</span><br><br>
			{$this->body}<br>
			</div><br>";
	}

	## Set up a curl call to the specified Mandrill endpoint and attach the API key to the data to be sent
	## Return the response data, or else return an error message if HTTP response code is not 200
	/**
	 * Set up a curl call to the specified Mandrill endpoint and attach the API key to the data to be sent
	 * Return the response data, or else return an error message if HTTP response code is not 200
	 * @param $data array
	 * @param $endpoint string
	 * @return string
	 */
	public static function sendMandrillRequest($data,$endpoint)
	{
		## Don't send if API key doesn't exist
		if(empty($GLOBALS["mandrill_api_key"])) return false;

		## Append API key to data to send
		$data["key"] = $GLOBALS["mandrill_api_key"];

		$data = http_build_query($data);
		$url = 'https://mandrillapp.com/api/1.0/'.$endpoint;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_POST,true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mandrill-Curl/1.0');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		$output = curl_exec($ch);
		$httpCode = curl_getinfo($ch,CURLINFO_RESPONSE_CODE);
		curl_close($ch);

		if($httpCode != 200) {
			$output = ["status" => "error","message" => "$url returned a status $httpCode :\r\n".var_export($output,true)];
			$output = json_encode($output);
		}

		## Return the response
		return $output;
	}

	// Replace any file download relative links with valid full links
	private function replaceFileDownloadLinks($recipientIsSurveyParticipant=false)
	{
		if (strpos($this->getBody(), "DataEntry/file_download.php") !== false || strpos($this->getBody(), "&__passthru=".urlencode("DataEntry/file_download.php")) !== false)
		{
			preg_match_all('/(href)=[\"\'](.+?)[\"\'].*?/i', $this->getBody(), $result);
			foreach ($result[2] as $key=>$href)
			{
				$hrefOrig = $href;
				// Parse the URL and validate its components
				$redirectUrlParts = parse_url($href);
				parse_str($redirectUrlParts['query'], $urlPart);
				if (!(isset($urlPart['id']) && isset($urlPart['pid']) && isinteger($urlPart['id'])
					&& isset($urlPart['doc_id_hash']) && isinteger($urlPart['pid']))) {
					continue;
				}
				$isDownloadLinkForm = (strpos($href, APP_PATH_WEBROOT."DataEntry/file_download.php") === 0);
				$isDownloadLinkSurvey = (strpos($href, APP_PATH_SURVEY) === 0 && strpos($href, "&__passthru=".urlencode("DataEntry/file_download.php")) !== false);
				if (!$isDownloadLinkForm && !$isDownloadLinkSurvey) continue;
				$edoc = $urlPart['id'];
				$doc_id_hash = $urlPart['doc_id_hash'];
				// If link has survey hash and recipient is not a survey participant, remove it up front (because we will validate it and re-add it later)
				if (isset($urlPart['s']) && $urlPart['s'] != '') {
					$href = str_replace("&s=".$urlPart['s'], "", $href);
					$href = str_replace("?s=".$urlPart['s'], "", $href);
				} else {
					$href = str_replace("?s=", "", $href);
					$href = str_replace("&s=", "", $href);
				}
				$surveyRecord = $urlPart['record'];
				$surveyEventId = $urlPart['event_id'];
				$surveyInstrument = $urlPart['page'];
				$surveyInstance = $urlPart['instance'];
				// Get project-level SALT using PID
				$pid = $urlPart['pid'];
				$pidSalt = Project::getProjectSalt($pid);
				// Validate doc id hash
				if ($doc_id_hash != Files::docIdHash($edoc, $pidSalt)) continue;
				// If the email recipient is a survey participant, make sure the download link is the survey version of the download link
				if ($recipientIsSurveyParticipant) {
					// Get the survey hash for this specific record/event/survey/instance
					$Proj = new Project($pid);
					if ($urlPart['s'] == '') {
						// The current "page" in the download link might not refer to a survey-enabled instrument, so if not, grab the instrument/event_id for any survey in the project as a suitable replacement
						if (!isset($Proj->forms[$surveyInstrument]['survey_id'])) {
							// "page" is not a survey, so grab the first available one in the project
							$allSurveyIds = array_keys($Proj->surveys);
							$randomSurveyId = array_shift($allSurveyIds);
							$surveyInstrument = $Proj->surveys[$randomSurveyId]['form_name'];
							$surveyInstance = 1;
							foreach ($Proj->eventsForms as $this_event_id=>$these_forms) {
								if (in_array($surveyInstrument, $these_forms)) {
									$surveyEventId = $this_event_id;
									break;
								}
							}
						}
					}
					// Use our survey parameters to build us a valid survey hash (and not a public one) to use in the download URL
					$surveyLinkForFile = REDCap::getSurveyLink($surveyRecord, $surveyInstrument, $surveyEventId, $surveyInstance, $pid);
					if ($surveyLinkForFile == null) continue;
					$redirectUrlParts2 = parse_url($surveyLinkForFile);
					parse_str($redirectUrlParts2['query'], $urlPartSurvey);
					if ($urlPartSurvey['s'] == '') continue;
					// We need to add __response_hash__ to the survey link
					$thisResponseHash = Survey::getResponseHashFromRecordEvent($surveyRecord, $surveyInstrument, $surveyEventId, $surveyInstance, $pid);
					$href .= "&__response_hash__=$thisResponseHash";
					// Rebuild download URL
					if ($isDownloadLinkForm) {
						$href = str_replace(APP_PATH_WEBROOT."DataEntry/file_download.php?", APP_PATH_SURVEY_FULL."?s={$urlPartSurvey['s']}&__passthru=".urlencode("DataEntry/file_download.php")."&", $href);
					} elseif ($isDownloadLinkSurvey) {
						$href = str_replace(APP_PATH_SURVEY."index.php?", APP_PATH_SURVEY_FULL."index.php?s={$urlPartSurvey['s']}&", $href);
						$href = str_replace(APP_PATH_SURVEY."?", APP_PATH_SURVEY_FULL."?s={$urlPartSurvey['s']}&", $href);
					}
				} else {
					// Rebuild download URL
					if ($isDownloadLinkForm) {
						$href = str_replace(APP_PATH_WEBROOT."DataEntry/file_download.php?", APP_PATH_WEBROOT_FULL."redcap_v".REDCAP_VERSION."/DataEntry/file_download.php?", $href);
					} elseif ($isDownloadLinkSurvey) {
						$href = str_replace(APP_PATH_SURVEY, APP_PATH_SURVEY_FULL, $href);
						// Re-add survey hash if link originally contained it
						$surveyLinkForFile = REDCap::getSurveyLink($surveyRecord, $surveyInstrument, $surveyEventId, $surveyInstance, $pid);
						$redirectUrlParts2 = parse_url($surveyLinkForFile);
						parse_str($redirectUrlParts2['query'], $urlPartSurvey);
						$href .= "&s=".$urlPartSurvey['s'];
						// We need to add __response_hash__ to the survey link
						$thisResponseHash = Survey::getResponseHashFromRecordEvent($surveyRecord, $surveyInstrument, $surveyEventId, $surveyInstance, $pid);
						$href .= "&__response_hash__=$thisResponseHash";
					}
				}
				// Perform the replace of the HREF link attribute
				if ($hrefOrig != $href) {
					$this->setBody(str_replace($hrefOrig, $href, $this->getBody()));
				}
			}
		}
	}
}
