<?php namespace Unsized\Crawl;


class Crawl{

function __construct($baseUrl='', $timeout=2)
		{
			$this->baseUrl=$baseUrl;
			$this->timeout=$timeout;
		}


function curlInit($user_agent="Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4",
$cookies='')

{
$options= array(
						    CURLOPT_TIMEOUT=> $this->timeout,
						    CURLOPT_COOKIESESSION=>TRUE,
						    CURLOPT_FOLLOWLOCATION=>TRUE,
						    CURLOPT_RETURNTRANSFER=>TRUE,
						    CURLOPT_SSL_VERIFYPEER =>FALSE,
						    CURLOPT_HEADER => FALSE,
								CURLOPT_USERAGENT => $user_agent,
								);

if (!empty($cookies)){
	$options[CURLOPT_COOKIEFILE] = $cookies;
	$options[CURLOPT_COOKIEJAR] = $cookies;
}


$this->ch = curl_init();    // initialize curl handle
curl_setopt_array ( $this->ch, $options);
}

function getWebPage($target="", $baseUrl="")
{
	if ($baseUrl==""){$baseUrl=$this->baseUrl;}
	curl_setopt ($this->ch, CURLOPT_POST, FALSE);
				  	// echo "<br>".$baseUrl.$target."<br>";
	curl_setopt($this->ch, CURLOPT_URL,$baseUrl.$target); // set url
	$this->webPage = curl_exec($this->ch);
	return $this->webPage;
	}


function postForm($fields, $target_url, $debug=false)
{
	$url=$this->baseUrl.$target_url;
	$post_fields=$this->postfields($fields); //print_r ($login_fields)
	curl_setopt($this->ch, CURLOPT_POST, 1); // set POST method
	curl_setopt($this->ch, CURLOPT_URL, $url); // set url to post to
	curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_fields); // add POST fields

		if ($debug){
		  $curl_log = fopen(APPPATH.'cookie/curl.txt', 'a');
		  curl_setopt($this->ch, CURLOPT_STDERR, $curl_log);
		  curl_setopt($this->ch, CURLOPT_VERBOSE,  TRUE);
		}

	//run the process
	$webPage = curl_exec($this->ch);
	return $webPage;
	}


function curlClose()
	{
		if (isset ($this->ch))
				{curl_close($this->ch);
					}
			}

/******* possible deprecation below here ******/


function getinfo()
{
echo $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
	}




function getFormElements($element,$by='name')
{
	$selectedElements=getFormElements($this->form, $element,  $by);
	return $selectedElements;
	}


function upload_photo($image_file, $input_name='file', $input_fields=array(), $form_action='', $submitName='' )
{
   if (file_exists($image_file))
        {
        $image_extention = preg_replace('/^.*\.([^.]+)$/D', '$1', $image_file);
        $photo_field[$input_name] = curl_file_create($image_file, 'image/'.$image_extention,'this_image');

				curl_setopt($this->ch, CURLOPT_HTTPHEADER,array('Content-Type: multipart/form-data'));
        curl_setopt($this->ch, CURLOPT_URL, $this->getFormAction() ); // set url to post to
        curl_setopt($this->ch, CURLOPT_POST, 1); // set POST method
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $photo_field+$input_fields+$this->getPresetValues($submitName) ); // add POST fields
        $this->webPage = curl_exec($this->ch);
        }
    else {
        echo "no such file $image_file";
        return FALSE;
        }

   return $this->webPage;
}




function getForms()
{
if ($this->webPage!=""){
    //echo "get raw Forms";
		$this->forms=getRawForms($this->webPage);
		}
else {
		echo "You need to get the webPage before we can get the form!
			select the form by name or id.  (using method crawl: parseForm). <br>" ;
			}
	return $this->forms;
	}

function formSummary() //takes the latest page and summarises the form elements
	{
		$this->getforms();
		echo "<pre>".htmlentities(print_r($this->forms,1))."</pre><br>";
	}


function parseForm($formId="",  $identifier="id")
	{
	$x=0;	$formFound=0;
	if ($this->webPage!=""){
		$this->getforms();
		$noOfForms=count( $this->forms);


		if ( ($formId=="") && ( $noOfForms == 1) ){
					$this->form=$this->forms[0];
					}
		else {

				foreach ($this->forms AS $formArray){
					//need to make sure array keys/ value for identifier are being stored lower case - or it wont match
					//	echo "<br>".$formArray['form_tag'][$identifier]."  formId: ".$formId;
						if  (  (isset ($formArray['form_tag'][$identifier])) && ($formArray['form_tag'][$identifier]==$formId)  ){
							$this->form=$formArray;
							$formFound=1;
							}
						$x++;
					}
					if ($formFound!=1){
						echo "Error: The form cannot be found<br>form-".$identifier."=".$formId."<br>";
									//$this->showForm();
                                    echo "<br>formTag: ".$formArray['form_tag'][$identifier];

									$this->form=0;
						}
				}
				if  ($noOfForms==0 ) {
					echo "Error: There were no forms found";
					}
		}//if webpage
		else echo "Error: No webPage cant parse form";
	return $this->form;
	}


//do a test submit??


function submitForm($enteredFields=array(), $submitName="" )
	{
	if (!$this->crawlError)
		{
		//need to check $enteredFields is an array
		if (is_array($enteredFields))
			{
		if ($this->form)
		  {
			if ($this->getFormMethod()=='POST')
				{
				//echo "Form Action: ". $this->getFormAction()."<br />";
				curl_setopt($this->ch, CURLOPT_URL, $this->getFormAction() ); // set url to post to
				curl_setopt($this->ch, CURLOPT_POST, 1); // set POST method
				curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->postfields($enteredFields+$this->getPresetValues($submitName))  ); // add POST fields

				$fields=$enteredFields+$this->getPresetValues($submitName);

				echo nl2br (print_r($fields,1 )) ;
				//curl_setopt($this->ch, CURLOPT_POSTFIELDS, ($enteredFields+$this->getPresetValues($submitName))  ); // add POST fields

				//curl_setopt($this->ch, CURLOPT_VERBOSE, true);
				//$verbose = fopen('php://temp', 'rw+');
				//curl_setopt($this->ch, CURLOPT_STDERR, $verbose);
				}

			//echo $this->getFormMethod();
			if ($this->getFormMethod()=='GET')
				{
				$this->getPresetValues($submitName);
				echo $url= $this->getFormAction()."?".$this->postfields($enteredFields+$this->getPresetValues($submitName));
				curl_setopt($this->ch, CURLOPT_URL,$url);
				}

			$this->webPage = curl_exec($this->ch); // run the whole process
			//$result = curl_exec($curlHandle);
				//if ($this->webPage  === FALSE) {
				//printf("cUrl error (#%d): %s<br>\n", curl_errno($this->ch),
				//htmlspecialchars(curl_error($this->ch)));
				//}

				//rewind($verbose);
				//$verboseLog = stream_get_contents($verbose);

				//echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";

			if ($this->verbose)
				{
				$this->vbOutput.= "<br>Form Summary<br>";
				$this->vbOutput.= "<br>Form action: ".$this->getFormAction()." <br> ";
				//$this->vbOutput.= "<br>Name/Value: "; print_array ($this->formElements);
				$this->vbOutput.= "<br>Entered Fields:"; print_array ($enteredFields);
				$this->vbOutput.= "<br>Empty Fields: "; print_array($this->emptyFormElements);
				$this->vbOutput.= "<br>Submit: "; print_array ($this->submit);
				$this->vbOutput.= "<br>Post Fields: ".$this->postfields($enteredFields+$this->getPresetValues($submitName))." <br> ";
				//$this->vbOutput.=$this->webPage;
				echo $this->vbOutput;
				}
			return $this->webPage;
			}
			else
				{$this->crawlError= "Error: Need to parse form first: method parseForm(formName,  formIdentifier))<br>";}
		}//is array
		else
				{$this->crawlError= "ERROR: Entered fields are not in an array format [SubmitForm]  crawl.php<br>";}
	}
}


function getFormAction()
	{
		$this->formAction=trim($this->form['form_tag']['action']);
			if (substr($this->formAction, 0,4)!="http")
			{
			$this->formAction=$this->baseUrl.$this->formAction; //need to investigate if begin with ../ or ./ or / find out pageURL
			}
	return $this->formAction;
	}

function getFormMethod()
	{
      if (!isset($this->form['form_tag']['method'])){
          echo "<br>***Warning- Form Method not found defaulting to GET ****<br>";
            $this->form['form_tag']['method']='GET';
            }

		//need to make it case insensitive
		$this->formMethod=strtoupper($this->form['form_tag']['method']);
		if (($this->formMethod == 'GET' ) OR ($this->formMethod == 'POST' ))
			{return $this->formMethod;}
		else {
					return 0;}
	}

function getPresetValues($submitName="")
	{
	$presetValues=0; $allSubmit=array(); $emptyFields=array(); $nameValue=array();

	if ($this->form)
		  {
			foreach ($this->form AS $FormElement)
				{
				if (isset($FormElement['type']))
					{
					switch ($FormElement['type'])
						{
							case "submit": case "button":
							if (isset ($FormElement['name'] )) {$name=$FormElement['name'];}
							elseif (isset ($FormElement['id'] )){$name=$FormElement['id'];}
							else {$name="";}
							if  (isset ($FormElement['value'])){
							$allSubmit[$name]=$FormElement['value'];}
							else {$allSubmit[$name]='';} //note this line added to fet rid of errors. May require rewrite
							break;

							default:
							if ( (isset ($FormElement['value'])) && ($FormElement['value']!="") )
								{$nameValue[$FormElement['name']]=$FormElement['value'];}
							else
								{
                                if (isset ($FormElement['name'])){
                                    $emptyFields[]=$FormElement['name']; }
                                    }
							break;
						}
					}
				}
			//
			switch (count($allSubmit))
						{
							case 0:
							echo "Error: No submit types or buttons found! <br>
									   check Raw form code and regular expressions in parser!<br>";
										$submit=array();
							break;

							case 1:
										$submit=$allSubmit;
							break;

							default:  //more than ne button/ submit

							if ( $submitName != ""  )
								{
								if (isset ($allSubmit[$submitName]))
									{$submit=array($submitName=>$allSubmit[$submitName]);
									}
									else {echo "Cant find submit name: ".$submitName.
													"<br>options are  as follows:<br>"; print_array ($allSubmit);}
									}
								elseif (  $submitName == "" )
									{
										echo "error: There is more than one button <br>
										need to select one in method 'getPresetValues'<br>
										options are <br>".htmlentities(print_r($allSubmit));
										return 0;
									}
							break;
						}

			$presetValues=$submit+$nameValue;
			$this->emptyFormElements=$emptyFields;
			$this->formElements=$nameValue;
			$this->submit=$submit;
			}
			else
			{echo "error: need to getForm using method..   first!";}
	return $presetValues;
	}


function countFieldType($type)
	{
		$count=FALSE;
		if ($this->form)
			{
				$count=count($this->form[$type]);
				}
		return $count;
		}

function countButtons()
	{
		return countFieldType("submit")+countFieldType("button");
		}

function findString($string, $echo=0)
		{
			if (!$this->crawlError)
				{
				$pos=strpos ($this->webPage, $string);
				if ($pos === false)
					{$found=0;
					$this->crawlError= "<br><b>NOT FOUND: </b>".$string;
					echo $this->webPage;
					}
				else {$found=1;}
				if (($this->verbose) OR ($echo==1))
					{
					if ($found){echo "<br>Found: ".$string;}
					else {
							echo "<br>FAILED - String not found: ".$string."<br>";
							echo $this->webPage;
							}
					}
			return $found;
				}
		}

function findPattern($pattern) //searches for a single pattern and returns a single result
{

	if (preg_match ($pattern, $this->webPage, $match))
		{
		return $match[1];
			}

	else {
		echo "cant find pattern <br> $pattern <br> $this->webpage";
		return FALSE;}
}



function findStringOnErrorPage($string,  $echo="1")
	{
		$pos=strpos ($this->webPage, $string);
				if ($pos === false)
					{$found=0;
					}
				else {$found=1;}
				if (($this->verbose) OR ($echo==1))
					{
					if ($found){echo "<br>Found: ".$string;}
					else {echo "<br>FAILED - String not found: ".$string;}
					}
			return $found;
		}


//needs rewrite as getLinkArray is a helper
function clickOnLink($linkTitle)
{
	//note can have issues with special characters - check link array for actual content
	$return=0;
	if (!$this->crawlError)
		{

		$linkArray=$this->getLinkArray();

		$newTarget=$this->getLink($linkTitle);
		if ($newTarget)
			{
			$newTarget=$this->checkTarget($newTarget); // if URL is absolute, change to relative path
			$this->getWebPage($newTarget);
			}
		else {
				 echo $this->crawlError="<br>Cant find Link Title: ".$linkTitle;
				 echo nl2br(print_r($linkArray,1) );
				 $return =0;}

		if ($this->verbose)
			{
			echo $this->crawlError;
			echo "<br>getWebPageFromLink : $linkTitle - New Target - $newTarget <br>";
			}

		$return = $this->webPage;
		}
return $return;
}

//function just puts in correct format of "enteredFields" in submit button
function clickCheckBox($deleteField)
	{
		//really need to check if check box exists
		//better to somehow add to internals of submitForm
		$checkedField = array($deleteField=>"1");
		return $checkedField;
		}


function getWebPageFromLink($linkTitle,$url="")
{
		$this->getWebPageLinks($url);
		$newTarget=$this->getLink($linkTitle);
		$newTarget=$this->checkTarget($newTarget); // if URL is absolute, change to relative path
		$this->getWebPage($newTarget);

	if ($this->verbose)
		{
			echo "<br>getWebPageFromLink : $linkTitle - New Target - $newTarget <br>";
			}
	return $this->webPage;
}

function getLink($linkTitle)
{
	$this->target=0;
	if ($this->linkArray)
		{
			$linkTitles=$this->linkArray['3'];
			$linkTargets=$this->linkArray['2'];

			if ($linkTitleNo=array_search ( $linkTitle, $linkTitles ))  //if link title is found get the key
				{


				$this->target=$linkTargets[$linkTitleNo];
				}
			elseif ($this->verbose)
				{
				echo "<br>Can' t find link for  $linkTitle <br>";
				echo $this->webPage; //print_array($this->linkArray);
				return 0;
				}
		}
	else
		{$this->target=0;
		$error="<br>linkArray not set - run getlinkArray<br>";
		return 0;
		}
	if ($this->verbose)
			{echo "<br>getLink : ".$linkTitle."  -  ". $this->target."<br>";}

	return $this->target;
}


function linkTargetContains ($needle)
{
	$target=linkTargetContains ( $this->getLinkArray(), $needle);

	if (!$target){
		echo "<br />ERROR: Cant find $needle in any of the link targets (urls)";
		}
	return $target;
	}

function getLinkArray()
{
	$this->linkArray=getLinkArray($this->webPage);
	return $this->linkArray;
	}

function showWebPageLinks()
{
		echo "<br>LINK ARRAY<br>";
		print_array($this->getLinkArray() );

return $this->linkArray;
}




function checkTarget($target)
{
	$target=trim($target);
	//checks the target URL, to see if it is relative or absolute
	//If absolute, check to see if same as base url
	//if same the output is changed to relative url
	if (substr($target, 0,4)=="http")
		{
		$target=substr($target,strlen($this->baseUrl));
		}

	if ($this->verbose)
		{echo "<br>Target:-  $target <br>";
		  }
		  return $target;
}

function absFormAction($target)
{
	$target=trim($target);
	//checks the target URL, to see if it is relative or absolute
	//If absolute, check to see if same as base url
	//if same the output is changed to relative url
	if (!substr($target, 0,4)=="http")
		{
		$target=$this->baseUrl.$target;
		}

	if ($this->verbose)
		{echo "<br>Form Action Target:-  $target <br>";
		  }
		  return $target;
}

function postfields($fields=array())
{
//Change array to $PostFields
 	$postFields=""; $x=0;
 	foreach ($fields AS $name=>$formElements)
 			{
 			if ( ($formElements !="") && (!is_array($formElements)) )
 				{
 				$name."=".urlencode($formElements)."<br>";
 				if ($x==0)
 					{$postFields=$name."=".urlencode($formElements);}
 				else
 					{$postFields=$postFields."&".$postFields=$name."=".urlencode($formElements);}
 				$x++;
 				}
			}
return $postFields;
}


function curlError()
{
	return curl_error($this->ch );
	}

/***************xpath********************/

function parseForm2($form_key="",  $identifier="id")

{
    //helper does the hard work
    $results=parse_form($this->webPage, $form_key, $identifier);

    //formAction
    if (substr($results['form']['action'], 0,4)!="http"){
        $this->formAction=$this->baseUrl.$results['form']['action']; //need to investigate if begin with ../ or ./ or / find out pageURL
        }else{
        $this->formAction=$results['form']['action'];
        }
        //formMethod
        $this->formMethod=$results['form']['method'];
        //formPresetValues
        $this->formPresetValues=$results['preset'];
        //print_r($results);
        return $results;
        }

function submitForm2($enteredFields=array() )
{
//need to check $enteredFields is an array
		if (is_array($enteredFields))
			{
			if ($this->formMethod=='POST')
				{

                $post_fields=$this->postfields(array_merge($this->formPresetValues, $enteredFields));

				//echo "Form Action: ". $this->getFormAction()."<br />";
				curl_setopt($this->ch, CURLOPT_URL, $this->formAction ); // set url to post to
				curl_setopt($this->ch, CURLOPT_POST, 1); // set POST method
				curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->postfields(array_merge($this->formPresetValues, $enteredFields)) ); // add POST fields

				//$fields=$enteredFields+$this->formPresetValues;
				//echo nl2br (print_r($fields,1 )) ;
                }

            if ($this->formMethod=='GET')
				{
				//$this->getPresetValues($submitName);
				//echo
                $url= $this->formAction."?".$this->postfields(array_merge($this->formPresetValues, $enteredFields));
				curl_setopt($this->ch, CURLOPT_URL,$url);
				}

            echo "<br>Submitting Form<br>";
			return $this->webPage = curl_exec($this->ch);
            }//isset entered fields
}







function set_formMethod($method)
{
    $this->formMethod=strtoupper($method);
    }

function set_formAction($action)
{
    $this->formAction=$action;
    }

function set_presetValues($presetValues)
{
    $this->presetValues=$presetValues;
    }




function upload_photo_manual($image_file, $input_name='file', $input_fields=array(), $form_action='' )
{
   if (file_exists($image_file))
        {
        $image_extention = preg_replace('/^.*\.([^.]+)$/D', '$1', $image_file);
        $photo_field[$input_name] = curl_file_create($image_file, 'image/'.$image_extention,'this_image');

				curl_setopt($this->ch, CURLOPT_HTTPHEADER,array('Content-Type: multipart/form-data'));
        curl_setopt($this->ch, CURLOPT_URL, $this->baseUrl.$form_action ); // set url to post to
        curl_setopt($this->ch, CURLOPT_POST, 1); // set POST method
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $photo_field+$input_fields); // add POST fields
        $this->webPage = curl_exec($this->ch);
        }
    else {
        echo "no such file $image_file";
        return FALSE;
        }

   return $this->webPage;
}



}//end class

function print_array($array)
	{
		$array=print_r ($array, TRUE);
		echo "<pre>".htmlentities($array)."</pre><br>";
		}




?>
