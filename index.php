<?php

	class Parsedown{const version='1.6.0';function text($text){$this->DefinitionData=array();$text=str_replace(array("\r\n","\r"),"\n",$text);$text=trim($text,"\n");$lines=explode("\n",$text);$markup=$this->lines($lines);$markup=trim($markup,"\n");return $markup;}function setBreaksEnabled($breaksEnabled){$this->breaksEnabled=$breaksEnabled;return $this;}protected $breaksEnabled;function setMarkupEscaped($markupEscaped){$this->markupEscaped=$markupEscaped;return $this;}protected $markupEscaped;function setUrlsLinked($urlsLinked){$this->urlsLinked=$urlsLinked;return $this;}protected $urlsLinked=true;protected $BlockTypes=array('#'=>array('Header'),'*'=>array('Rule','List'),'+'=>array('List'),'-'=>array('SetextHeader','Table','Rule','List'),'0'=>array('List'),'1'=>array('List'),'2'=>array('List'),'3'=>array('List'),'4'=>array('List'),'5'=>array('List'),'6'=>array('List'),'7'=>array('List'),'8'=>array('List'),'9'=>array('List'),':'=>array('Table'),'<'=>array('Comment','Markup'),'='=>array('SetextHeader'),'>'=>array('Quote'),'['=>array('Reference'),'_'=>array('Rule'),'`'=>array('FencedCode'),'|'=>array('Table'),'~'=>array('FencedCode'),);protected $unmarkedBlockTypes=array('Code',);protected function lines(array $lines){$CurrentBlock=null;foreach($lines as $line){if(chop($line)===''){if(isset($CurrentBlock)){$CurrentBlock['interrupted']=true;}continue;}if(strpos($line,"\t")!==false){$parts=explode("\t",$line);$line=$parts[0];unset($parts[0]);foreach($parts as $part){$shortage=4 - mb_strlen($line,'utf-8')% 4;$line.=str_repeat(' ',$shortage);$line.=$part;}}$indent=0;while(isset($line[$indent])and $line[$indent]===' '){$indent ++;}$text=$indent>0?substr($line,$indent):$line;$Line=array('body'=>$line,'indent'=>$indent,'text'=>$text);if(isset($CurrentBlock['continuable'])){$Block=$this->{'block'.$CurrentBlock['type'].'Continue'}($Line,$CurrentBlock);if(isset($Block)){$CurrentBlock=$Block;continue;}else{if($this->isBlockCompletable($CurrentBlock['type'])){$CurrentBlock=$this->{'block'.$CurrentBlock['type'].'Complete'}($CurrentBlock);}}}$marker=$text[0];$blockTypes=$this->unmarkedBlockTypes;if(isset($this->BlockTypes[$marker])){foreach($this->BlockTypes[$marker] as $blockType){$blockTypes []=$blockType;}}foreach($blockTypes as $blockType){$Block=$this->{'block'.$blockType}($Line,$CurrentBlock);if(isset($Block)){$Block['type']=$blockType;if(! isset($Block['identified'])){$Blocks []=$CurrentBlock;$Block['identified']=true;}if($this->isBlockContinuable($blockType)){$Block['continuable']=true;}$CurrentBlock=$Block;continue 2;}}if(isset($CurrentBlock)and ! isset($CurrentBlock['type'])and ! isset($CurrentBlock['interrupted'])){$CurrentBlock['element']['text'].="\n".$text;}else{$Blocks []=$CurrentBlock;$CurrentBlock=$this->paragraph($Line);$CurrentBlock['identified']=true;}}if(isset($CurrentBlock['continuable'])and $this->isBlockCompletable($CurrentBlock['type'])){$CurrentBlock=$this->{'block'.$CurrentBlock['type'].'Complete'}($CurrentBlock);}$Blocks []=$CurrentBlock;unset($Blocks[0]);$markup='';foreach($Blocks as $Block){if(isset($Block['hidden'])){continue;}$markup.="\n";$markup.=isset($Block['markup'])?$Block['markup']:$this->element($Block['element']);}$markup.="\n";return $markup;}protected function isBlockContinuable($Type){return method_exists($this,'block'.$Type.'Continue');}protected function isBlockCompletable($Type){return method_exists($this,'block'.$Type.'Complete');}protected function blockCode($Line,$Block=null){if(isset($Block)and ! isset($Block['type'])and ! isset($Block['interrupted'])){return;}if($Line['indent']>=4){$text=substr($Line['body'],4);$Block=array('element'=>array('name'=>'pre','handler'=>'element','text'=>array('name'=>'code','text'=>$text,),),);return $Block;}}protected function blockCodeContinue($Line,$Block){if($Line['indent']>=4){if(isset($Block['interrupted'])){$Block['element']['text']['text'].="\n";unset($Block['interrupted']);}$Block['element']['text']['text'].="\n";$text=substr($Line['body'],4);$Block['element']['text']['text'].=$text;return $Block;}}protected function blockCodeComplete($Block){$text=$Block['element']['text']['text'];$text=htmlspecialchars($text,ENT_NOQUOTES,'UTF-8');$Block['element']['text']['text']=$text;return $Block;}protected function blockComment($Line){if($this->markupEscaped){return;}if(isset($Line['text'][3])and $Line['text'][3]==='-' and $Line['text'][2]==='-' and $Line['text'][1]==='!'){$Block=array('markup'=>$Line['body'],);if(preg_match('/-->$/',$Line['text'])){$Block['closed']=true;}return $Block;}}protected function blockCommentContinue($Line,array $Block){if(isset($Block['closed'])){return;}$Block['markup'].="\n".$Line['body'];if(preg_match('/-->$/',$Line['text'])){$Block['closed']=true;}return $Block;}protected function blockFencedCode($Line){if(preg_match('/^['.$Line['text'][0].']{3,}[ ]*([\w-]+)?[ ]*$/',$Line['text'],$matches)){$Element=array('name'=>'code','text'=>'',);if(isset($matches[1])){$class='language-'.$matches[1];$Element['attributes']=array('class'=>$class,);}$Block=array('char'=>$Line['text'][0],'element'=>array('name'=>'pre','handler'=>'element','text'=>$Element,),);return $Block;}}protected function blockFencedCodeContinue($Line,$Block){if(isset($Block['complete'])){return;}if(isset($Block['interrupted'])){$Block['element']['text']['text'].="\n";unset($Block['interrupted']);}if(preg_match('/^'.$Block['char'].'{3,}[ ]*$/',$Line['text'])){$Block['element']['text']['text']=substr($Block['element']['text']['text'],1);$Block['complete']=true;return $Block;}$Block['element']['text']['text'].="\n".$Line['body'];;return $Block;}protected function blockFencedCodeComplete($Block){$text=$Block['element']['text']['text'];$text=htmlspecialchars($text,ENT_NOQUOTES,'UTF-8');$Block['element']['text']['text']=$text;return $Block;}protected function blockHeader($Line){if(isset($Line['text'][1])){$level=1;while(isset($Line['text'][$level])and $Line['text'][$level]==='#'){$level ++;}if($level>6){return;}$text=trim($Line['text'],'# ');$Block=array('element'=>array('name'=>'h'.min(6,$level),'text'=>$text,'handler'=>'line',),);return $Block;}}protected function blockList($Line){list($name,$pattern)=$Line['text'][0]<='-'?array('ul','[*+-]'):array('ol','[0-9]+[.]');if(preg_match('/^('.$pattern.'[ ]+)(.*)/',$Line['text'],$matches)){$Block=array('indent'=>$Line['indent'],'pattern'=>$pattern,'element'=>array('name'=>$name,'handler'=>'elements',),);$Block['li']=array('name'=>'li','handler'=>'li','text'=>array($matches[2],),);$Block['element']['text'] []=& $Block['li'];return $Block;}}protected function blockListContinue($Line,array $Block){if($Block['indent']===$Line['indent'] and preg_match('/^'.$Block['pattern'].'(?:[ ]+(.*)|$)/',$Line['text'],$matches)){if(isset($Block['interrupted'])){$Block['li']['text'] []='';unset($Block['interrupted']);}unset($Block['li']);$text=isset($matches[1])?$matches[1]:'';$Block['li']=array('name'=>'li','handler'=>'li','text'=>array($text,),);$Block['element']['text'] []=& $Block['li'];return $Block;}if($Line['text'][0]==='[' and $this->blockReference($Line)){return $Block;}if(! isset($Block['interrupted'])){$text=preg_replace('/^[ ]{0,4}/','',$Line['body']);$Block['li']['text'] []=$text;return $Block;}if($Line['indent']>0){$Block['li']['text'] []='';$text=preg_replace('/^[ ]{0,4}/','',$Line['body']);$Block['li']['text'] []=$text;unset($Block['interrupted']);return $Block;}}protected function blockQuote($Line){if(preg_match('/^>[ ]?(.*)/',$Line['text'],$matches)){$Block=array('element'=>array('name'=>'blockquote','handler'=>'lines','text'=>(array)$matches[1],),);return $Block;}}protected function blockQuoteContinue($Line,array $Block){if($Line['text'][0]==='>' and preg_match('/^>[ ]?(.*)/',$Line['text'],$matches)){if(isset($Block['interrupted'])){$Block['element']['text'] []='';unset($Block['interrupted']);}$Block['element']['text'] []=$matches[1];return $Block;}if(! isset($Block['interrupted'])){$Block['element']['text'] []=$Line['text'];return $Block;}}protected function blockRule($Line){if(preg_match('/^(['.$Line['text'][0].'])([ ]*\1){2,}[ ]*$/',$Line['text'])){$Block=array('element'=>array('name'=>'hr'),);return $Block;}}protected function blockSetextHeader($Line,array $Block=null){if(! isset($Block)or isset($Block['type'])or isset($Block['interrupted'])){return;}if(chop($Line['text'],$Line['text'][0])===''){$Block['element']['name']=$Line['text'][0]==='='?'h1':'h2';return $Block;}}protected function blockMarkup($Line){if($this->markupEscaped){return;}if(preg_match('/^<(\w*)(?:[ ]*'.$this->regexHtmlAttribute.')*[ ]*(\/)?>/',$Line['text'],$matches)){$element=strtolower($matches[1]);if(in_array($element,$this->textLevelElements)){return;}$Block=array('name'=>$matches[1],'depth'=>0,'markup'=>$Line['text'],);$length=strlen($matches[0]);$remainder=substr($Line['text'],$length);if(trim($remainder)===''){if(isset($matches[2])or in_array($matches[1],$this->voidElements)){$Block['closed']=true;$Block['void']=true;}}else{if(isset($matches[2])or in_array($matches[1],$this->voidElements)){return;}if(preg_match('/<\/'.$matches[1].'>[ ]*$/i',$remainder)){$Block['closed']=true;}}return $Block;}}protected function blockMarkupContinue($Line,array $Block){if(isset($Block['closed'])){return;}if(preg_match('/^<'.$Block['name'].'(?:[ ]*'.$this->regexHtmlAttribute.')*[ ]*>/i',$Line['text'])){$Block['depth'] ++;}if(preg_match('/(.*?)<\/'.$Block['name'].'>[ ]*$/i',$Line['text'],$matches)){if($Block['depth']>0){$Block['depth'] --;}else{$Block['closed']=true;}}if(isset($Block['interrupted'])){$Block['markup'].="\n";unset($Block['interrupted']);}$Block['markup'].="\n".$Line['body'];return $Block;}protected function blockReference($Line){if(preg_match('/^\[(.+?)\]:[ ]*<?(\S+?)>?(?:[ ]+["\'(](.+)["\')])?[ ]*$/',$Line['text'],$matches)){$id=strtolower($matches[1]);$Data=array('url'=>$matches[2],'title'=>null,);if(isset($matches[3])){$Data['title']=$matches[3];}$this->DefinitionData['Reference'][$id]=$Data;$Block=array('hidden'=>true,);return $Block;}}protected function blockTable($Line,array $Block=null){if(! isset($Block)or isset($Block['type'])or isset($Block['interrupted'])){return;}if(strpos($Block['element']['text'],'|')!==false and chop($Line['text'],' -:|')===''){$alignments=array();$divider=$Line['text'];$divider=trim($divider);$divider=trim($divider,'|');$dividerCells=explode('|',$divider);foreach($dividerCells as $dividerCell){$dividerCell=trim($dividerCell);if($dividerCell===''){continue;}$alignment=null;if($dividerCell[0]===':'){$alignment='left';}if(substr($dividerCell,- 1)===':'){$alignment=$alignment==='left'?'center':'right';}$alignments []=$alignment;}$HeaderElements=array();$header=$Block['element']['text'];$header=trim($header);$header=trim($header,'|');$headerCells=explode('|',$header);foreach($headerCells as $index=>$headerCell){$headerCell=trim($headerCell);$HeaderElement=array('name'=>'th','text'=>$headerCell,'handler'=>'line',);if(isset($alignments[$index])){$alignment=$alignments[$index];$HeaderElement['attributes']=array('style'=>'text-align: '.$alignment.';',);}$HeaderElements []=$HeaderElement;}$Block=array('alignments'=>$alignments,'identified'=>true,'element'=>array('name'=>'table','handler'=>'elements',),);$Block['element']['text'] []=array('name'=>'thead','handler'=>'elements',);$Block['element']['text'] []=array('name'=>'tbody','handler'=>'elements','text'=>array(),);$Block['element']['text'][0]['text'] []=array('name'=>'tr','handler'=>'elements','text'=>$HeaderElements,);return $Block;}}protected function blockTableContinue($Line,array $Block){if(isset($Block['interrupted'])){return;}if($Line['text'][0]==='|' or strpos($Line['text'],'|')){$Elements=array();$row=$Line['text'];$row=trim($row);$row=trim($row,'|');preg_match_all('/(?:(\\\\[|])|[^|`]|`[^`]+`|`)+/',$row,$matches);foreach($matches[0] as $index=>$cell){$cell=trim($cell);$Element=array('name'=>'td','handler'=>'line','text'=>$cell,);if(isset($Block['alignments'][$index])){$Element['attributes']=array('style'=>'text-align: '.$Block['alignments'][$index].';',);}$Elements []=$Element;}$Element=array('name'=>'tr','handler'=>'elements','text'=>$Elements,);$Block['element']['text'][1]['text'] []=$Element;return $Block;}}protected function paragraph($Line){$Block=array('element'=>array('name'=>'p','text'=>$Line['text'],'handler'=>'line',),);return $Block;}protected $InlineTypes=array('"'=>array('SpecialCharacter'),'!'=>array('Image'),'&'=>array('SpecialCharacter'),'*'=>array('Emphasis'),':'=>array('Url'),'<'=>array('UrlTag','EmailTag','Markup','SpecialCharacter'),'>'=>array('SpecialCharacter'),'['=>array('Link'),'_'=>array('Emphasis'),'`'=>array('Code'),'~'=>array('Strikethrough'),'\\'=>array('EscapeSequence'),);protected $inlineMarkerList='!"*_&[:<>`~\\';public function line($text){$markup='';while($excerpt=strpbrk($text,$this->inlineMarkerList)){$marker=$excerpt[0];$markerPosition=strpos($text,$marker);$Excerpt=array('text'=>$excerpt,'context'=>$text);foreach($this->InlineTypes[$marker] as $inlineType){$Inline=$this->{'inline'.$inlineType}($Excerpt);if(! isset($Inline)){continue;}if(isset($Inline['position'])and $Inline['position']>$markerPosition){continue;}if(! isset($Inline['position'])){$Inline['position']=$markerPosition;}$unmarkedText=substr($text,0,$Inline['position']);$markup.=$this->unmarkedText($unmarkedText);$markup.=isset($Inline['markup'])?$Inline['markup']:$this->element($Inline['element']);$text=substr($text,$Inline['position'] + $Inline['extent']);continue 2;}$unmarkedText=substr($text,0,$markerPosition + 1);$markup.=$this->unmarkedText($unmarkedText);$text=substr($text,$markerPosition + 1);}$markup.=$this->unmarkedText($text);return $markup;}protected function inlineCode($Excerpt){$marker=$Excerpt['text'][0];if(preg_match('/^('.$marker.'+)[ ]*(.+?)[ ]*(?<!'.$marker.')\1(?!'.$marker.')/s',$Excerpt['text'],$matches)){$text=$matches[2];$text=htmlspecialchars($text,ENT_NOQUOTES,'UTF-8');$text=preg_replace("/[ ]*\n/",' ',$text);return array('extent'=>strlen($matches[0]),'element'=>array('name'=>'code','text'=>$text,),);}}protected function inlineEmailTag($Excerpt){if(strpos($Excerpt['text'],'>')!==false and preg_match('/^<((mailto:)?\S+?@\S+?)>/i',$Excerpt['text'],$matches)){$url=$matches[1];if(! isset($matches[2])){$url='mailto:'.$url;}return array('extent'=>strlen($matches[0]),'element'=>array('name'=>'a','text'=>$matches[1],'attributes'=>array('href'=>$url,),),);}}protected function inlineEmphasis($Excerpt){if(! isset($Excerpt['text'][1])){return;}$marker=$Excerpt['text'][0];if($Excerpt['text'][1]===$marker and preg_match($this->StrongRegex[$marker],$Excerpt['text'],$matches)){$emphasis='strong';}elseif(preg_match($this->EmRegex[$marker],$Excerpt['text'],$matches)){$emphasis='em';}else{return;}return array('extent'=>strlen($matches[0]),'element'=>array('name'=>$emphasis,'handler'=>'line','text'=>$matches[1],),);}protected function inlineEscapeSequence($Excerpt){if(isset($Excerpt['text'][1])and in_array($Excerpt['text'][1],$this->specialCharacters)){return array('markup'=>$Excerpt['text'][1],'extent'=>2,);}}protected function inlineImage($Excerpt){if(! isset($Excerpt['text'][1])or $Excerpt['text'][1]!=='['){return;}$Excerpt['text']=substr($Excerpt['text'],1);$Link=$this->inlineLink($Excerpt);if($Link===null){return;}$Inline=array('extent'=>$Link['extent'] + 1,'element'=>array('name'=>'img','attributes'=>array('src'=>$Link['element']['attributes']['href'],'alt'=>$Link['element']['text'],),),);$Inline['element']['attributes']+=$Link['element']['attributes'];unset($Inline['element']['attributes']['href']);return $Inline;}protected function inlineLink($Excerpt){$Element=array('name'=>'a','handler'=>'line','text'=>null,'attributes'=>array('href'=>null,'title'=>null,),);$extent=0;$remainder=$Excerpt['text'];if(preg_match('/\[((?:[^][]|(?R))*)\]/',$remainder,$matches)){$Element['text']=$matches[1];$extent+=strlen($matches[0]);$remainder=substr($remainder,$extent);}else{return;}if(preg_match('/^[(]((?:[^ ()]|[(][^ )]+[)])+)(?:[ ]+("[^"]*"|\'[^\']*\'))?[)]/',$remainder,$matches)){$Element['attributes']['href']=$matches[1];if(isset($matches[2])){$Element['attributes']['title']=substr($matches[2],1,- 1);}$extent+=strlen($matches[0]);}else{if(preg_match('/^\s*\[(.*?)\]/',$remainder,$matches)){$definition=strlen($matches[1])?$matches[1]:$Element['text'];$definition=strtolower($definition);$extent+=strlen($matches[0]);}else{$definition=strtolower($Element['text']);}if(! isset($this->DefinitionData['Reference'][$definition])){return;}$Definition=$this->DefinitionData['Reference'][$definition];$Element['attributes']['href']=$Definition['url'];$Element['attributes']['title']=$Definition['title'];}$Element['attributes']['href']=str_replace(array('&','<'),array('&amp;','&lt;'),$Element['attributes']['href']);return array('extent'=>$extent,'element'=>$Element,);}protected function inlineMarkup($Excerpt){if($this->markupEscaped or strpos($Excerpt['text'],'>')===false){return;}if($Excerpt['text'][1]==='/' and preg_match('/^<\/\w*[ ]*>/s',$Excerpt['text'],$matches)){return array('markup'=>$matches[0],'extent'=>strlen($matches[0]),);}if($Excerpt['text'][1]==='!' and preg_match('/^<!---?[^>-](?:-?[^-])*-->/s',$Excerpt['text'],$matches)){return array('markup'=>$matches[0],'extent'=>strlen($matches[0]),);}if($Excerpt['text'][1]!==' ' and preg_match('/^<\w*(?:[ ]*'.$this->regexHtmlAttribute.')*[ ]*\/?>/s',$Excerpt['text'],$matches)){return array('markup'=>$matches[0],'extent'=>strlen($matches[0]),);}}protected function inlineSpecialCharacter($Excerpt){if($Excerpt['text'][0]==='&' and ! preg_match('/^&#?\w+;/',$Excerpt['text'])){return array('markup'=>'&amp;','extent'=>1,);}$SpecialCharacter=array('>'=>'gt','<'=>'lt','"'=>'quot');if(isset($SpecialCharacter[$Excerpt['text'][0]])){return array('markup'=>'&'.$SpecialCharacter[$Excerpt['text'][0]].';','extent'=>1,);}}protected function inlineStrikethrough($Excerpt){if(! isset($Excerpt['text'][1])){return;}if($Excerpt['text'][1]==='~' and preg_match('/^~~(?=\S)(.+?)(?<=\S)~~/',$Excerpt['text'],$matches)){return array('extent'=>strlen($matches[0]),'element'=>array('name'=>'del','text'=>$matches[1],'handler'=>'line',),);}}protected function inlineUrl($Excerpt){if($this->urlsLinked!==true or ! isset($Excerpt['text'][2])or $Excerpt['text'][2]!=='/'){return;}if(preg_match('/\bhttps?:[\/]{2}[^\s<]+\b\/*/ui',$Excerpt['context'],$matches,PREG_OFFSET_CAPTURE)){$Inline=array('extent'=>strlen($matches[0][0]),'position'=>$matches[0][1],'element'=>array('name'=>'a','text'=>$matches[0][0],'attributes'=>array('href'=>$matches[0][0],),),);return $Inline;}}protected function inlineUrlTag($Excerpt){if(strpos($Excerpt['text'],'>')!==false and preg_match('/^<(\w+:\/{2}[^ >]+)>/i',$Excerpt['text'],$matches)){$url=str_replace(array('&','<'),array('&amp;','&lt;'),$matches[1]);return array('extent'=>strlen($matches[0]),'element'=>array('name'=>'a','text'=>$url,'attributes'=>array('href'=>$url,),),);}}protected function unmarkedText($text){if($this->breaksEnabled){$text=preg_replace('/[ ]*\n/',"<br />\n",$text);}else{$text=preg_replace('/(?:[ ][ ]+|[ ]*\\\\)\n/',"<br />\n",$text);$text=str_replace(" \n","\n",$text);}return $text;}protected function element(array $Element){$markup='<'.$Element['name'];if(isset($Element['attributes'])){foreach($Element['attributes'] as $name=>$value){if($value===null){continue;}$markup.=' '.$name.'="'.$value.'"';}}if(isset($Element['text'])){$markup.='>';if(isset($Element['handler'])){$markup.=$this->{$Element['handler']}($Element['text']);}else{$markup.=$Element['text'];}$markup.='</'.$Element['name'].'>';}else{$markup.=' />';}return $markup;}protected function elements(array $Elements){$markup='';foreach($Elements as $Element){$markup.="\n".$this->element($Element);}$markup.="\n";return $markup;}protected function li($lines){$markup=$this->lines($lines);$trimmedMarkup=trim($markup);if(! in_array('',$lines)and substr($trimmedMarkup,0,3)==='<p>'){$markup=$trimmedMarkup;$markup=substr($markup,3);$position=strpos($markup,"</p>");$markup=substr_replace($markup,'',$position,4);}return $markup;}function parse($text){$markup=$this->text($text);return $markup;}static function instance($name='default'){if(isset(self::$instances[$name])){return self::$instances[$name];}$instance=new static();self::$instances[$name]=$instance;return $instance;}private static $instances=array();protected $DefinitionData;protected $specialCharacters=array('\\','`','*','_','{','}','[',']','(',')','>','#','+','-','.','!','|',);protected $StrongRegex=array('*'=>'/^[*]{2}((?:\\\\\*|[^*]|[*][^*]*[*])+?)[*]{2}(?![*])/s','_'=>'/^__((?:\\\\_|[^_]|_[^_]*_)+?)__(?!_)/us',);protected $EmRegex=array('*'=>'/^[*]((?:\\\\\*|[^*]|[*][*][^*]+?[*][*])+?)[*](?![*])/s','_'=>'/^_((?:\\\\_|[^_]|__[^_]*__)+?)_(?!_)\b/us',);protected $regexHtmlAttribute='[a-zA-Z_:][\w:.-]*(?:\s*=\s*(?:[^"\'=<>`\s]+|"[^"]*"|\'[^\']*\'))?';protected $voidElements=array('area','base','br','col','command','embed','hr','img','input','link','meta','param','source',);protected $textLevelElements=array('a','br','bdo','abbr','blink','nextid','acronym','basefont','b','em','big','cite','small','spacer','listing','i','rp','del','code','strike','marquee','q','rt','ins','font','strong','s','tt','sub','mark','u','xm','sup','nobr','var','ruby','wbr','span','time',);}
	
    class DirectoryLister {

		const VERSION = '2.6.1';

		protected $_directory     = null;
		protected $_appDir        = null;
		protected $_appURL        = null;
		protected $_fileTypes     = null;
		protected $_systemMessage = null;
		
		public function __construct() {

			if(!defined('__DIR__')) {
				define('__DIR__', dirname(__FILE__));
			}
			$this->_appDir = __DIR__;
			$this->_appURL = $this->_getAppUrl();

			$this->_config = Array(

				"default_title" => "ttyk",

				"hide_dot_files"            => true,
				"list_folders_first"        => true,
				"list_sort_order"           => "natcasesort",
				"index_subfolders" => false, //append "?dir=/path/" instead of going to folder path

				"hidden_files" => Array(
					".ht*",
					"*/.ht*",
					"index.md",
					"robots.txt"
				),
				"reverse_sort" => Array(
					// "path/to/folder"
				),
				"zip_dirs" => true,
				"zip_stream" => true,
				"zip_compression_level" => 0,
				"zip_disable" => Array(
					// "path/to/folder"
				),
			);			

			$FileTypes = Array(
				"file-archive-o" 	=> Array( "7z", "bz", "gz", "rar", "tar", "zip", "box", "deb", "rpm" ),
				"file-audio-o" 		=> Array( "aac", "flac", "mid", "midi", "mp3", "ogg", "wma", "wav" ),
				"file-code-o" 		=> Array( "c", "class", "cpp", "css", "erb", "htm", "html", "java", "js", "php", "pl", "py", "rb", "xhtml", "xml" ),
				"file-text-o" 		=> Array( "odt", "cfg", "ini", "log", "md", "rtf", "txt" ),
				"file-word-o" 		=> Array( "doc", "docx" ),
				"file-pdf-o" 		=> Array( "pdf" ),
				"file-excel-o" 		=> Array( "xls", "xlsx" ),
				"file-powerpoint-o" => Array( "pptx" ),
				"file-picture-o" 	=> Array( "ai", "drw", "eps", "ps", "svg" ),
				"youtube-play"	 	=> Array( "avi", "flv", "mkv", "mov", "mp4", "mpg", "ogv", "webm", "wmv", "swf" ),
				"file-image-o" 		=> Array( "bmp", "gif", "jpg", "jpeg", "png", "psd", "tga", "tif", "tiff" ),
				"fa-hdd-o" 			=> Array( "accdb", "db", "dbf", "mdb", "pdb", "sql", "csv" ),
				"gear" 				=> Array( "app", "bat", "com", "exe", "jar", "msi", "vb" ),
				"font" 				=> Array( "eot", "otf", "ttf", "woff", "woff2" ),
				"terminal" 			=> Array( "bat", "cmd", "sh" ),
				"fa-gamepad" 		=> Array( "gam", "nes", "ram" ),
				"floppy-o" 			=> Array( "sav", "bak" ),
				"envelope" 			=> Array( "msg" ),
				"file" 				=> Array( "blank" )
			);

			$this->_fileTypes = Array();

			foreach ( $FileTypes as $ImageObject => $Extensions ) {
				foreach ( $Extensions as $Extension ) {
					$this->_fileTypes[ $Extension ] = $ImageObject;	
				}					
			}

		}
		public function isZipEnabled() {
			foreach ( $this->_config['zip_disable'] as $disabledPath ) {
				if (fnmatch($disabledPath, $this->_directory)) {
					return false;
				}
			}
			return $this->_config['zip_dirs'];
		}
		public function zipDirectory($directory) {
			if ($this->_config['zip_dirs']) {
				$directory = $this->setDirectoryPath($directory);
				if ($directory != '.' && $this->_isHidden($directory)) {
					echo "Access denied.";
				}
				$filename_no_ext = basename($directory);
				if ($directory == '.') {
					$filename_no_ext = 'Home';
				}
				header('Content-Type: archive/zip');
				header("Content-Disposition: attachment; filename=\"$filename_no_ext.zip\"");
				chdir($directory);
				$exclude_list = implode(' ', array_merge($this->_config['hidden_files'], Array('index.php')));
				$exclude_list = str_replace("*", "\*", $exclude_list);
				if ($this->_config['zip_stream']) {
					$stream = popen('/usr/bin/zip -' . $this->_config['zip_compression_level'] . ' -r -q - * -x ' . $exclude_list, 'r');
					if ($stream) {
					   fpassthru($stream);
					   fclose($stream);
					}
				} else {
					$tmp_zip = tempnam('tmp', 'tempzip') . '.zip';
					exec('zip -' . $this->_config['zip_compression_level'] . ' -r ' . $tmp_zip . ' * -x ' . $exclude_list);
					$filesize = filesize($tmp_zip);
					header("Content-Length: $filesize");
					$fp = fopen($tmp_zip, 'r');
					echo fpassthru($fp);
					unlink($tmp_zip);
				}
			}
		}
		public function listDirectory($directory) {
			$directory = $this->setDirectoryPath($directory);
			if ($directory === null) {
				$directory = $this->_directory;
			}
			$directoryArray = $this->_readDirectory($directory);
			return $directoryArray;
		}
		public function listBreadcrumbs($directory = null) {
			global $GO_TO_SUBFOLDERS;
			$schema = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
			$breadcrumbsArray[] = Array(
				'link' => $schema.$_SERVER["SERVER_NAME"],
				'text' => 'Home'
			);
			$_directory = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);#$this->_directory;
			$_dirArray = explode('/', $_directory);
			array_shift( $_dirArray );
			$last_dir = array_pop( $_dirArray );
			$i = 0;
			foreach ($_dirArray as $key => $dir) {
				$i = $i+1;
				$_breadcrumbsArray[] = Array(
					'link' => str_repeat( "../", count($_dirArray) - $i ),
					'text' => $dir
				);
			}
			if( $_breadcrumbsArray != Null ){ 
				$breadcrumbsArray = array_merge( $breadcrumbsArray, $_breadcrumbsArray );
			}
			if ($directory === null) {
				$directory = $this->_directory;			
			}
			$dirArray = explode('/', $directory);	
			foreach ($dirArray as $key => $dir) {
				if ($dir != '.') {
					$dirPath  = null;
					
					for ($i = 0; $i <= $key; $i++) {
						$dirPath = $dirPath . $dirArray[$i] . '/';
					}
					if(substr($dirPath, -1) == '/') {
						$dirPath = substr($dirPath, 0, -1);
					}
					$link = $this->_appURL . '?dir=' . rawurlencode($dirPath);		
					$breadcrumbsArray[] = Array(
						'link' => $link,
						'text' => $dir
					);
					
				}
			}
			return $breadcrumbsArray;
			
		}
		public function containsIndex($dirPath) {
			foreach ($this->_config['index_files'] as $indexFile) {
				if (file_exists($dirPath . '/' . $indexFile)) {
					return true;
				}
			}
			return false;
		}
		public function getListedPath() {
			if ($this->_directory == '.') {
				$path = $this->_appURL;
			} else {
				$path = $this->_appURL . $this->_directory;
			}
			return $path;
		}
		public function externalLinksNewWindow() {
			return $this->_config['external_links_new_window'];
		}
		public function getSystemMessages() {
			if (isset($this->_systemMessage) && is_array($this->_systemMessage)) {
				return $this->_systemMessage;
			} else {
				return false;
			}
		}
		function getFileSize($filePath) {
			$bytes = filesize($filePath);
			$sizes = Array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
			$factor = floor((strlen($bytes) - 1) / 3);
			$fileSize = sprintf('%.2f', $bytes / pow(1024, $factor)) . $sizes[$factor];
			return $fileSize;
		}
		public function getFileHash($filePath) {
			$hashArray = array();
			if (!file_exists($filePath)) {
				return json_encode($hashArray);
			}
			if ($this->_isHidden($filePath)) {
				return json_encode($hashArray);
			}
			if (strpos($filePath, '<') !== false || strpos($filePath, '>') !== false
			|| strpos($filePath, '..') !== false || strpos($filePath, '/') === 0) {
				return json_encode($hashArray);
			}
			if (filesize($filePath) > $this->_config['hash_size_limit']) {
				$hashArray['md5']  = '[ File size exceeds threshold ]';
				$hashArray['sha1'] = '[ File size exceeds threshold ]';
			} else {
				$hashArray['md5']  = hash_file('md5', $filePath);
				$hashArray['sha1'] = hash_file('sha1', $filePath);
			}
			return $hashArray;
		}
		public function setDirectoryPath($path = null) {
			$this->_directory = $this->_setDirectoryPath($path);
			return $this->_directory;
		}
		public function getDirectoryPath() {
			return $this->_directory;
		}
		public function setSystemMessage($type, $text) {
			if (isset($this->_systemMessage) && !is_array($this->_systemMessage)) {
				$this->_systemMessage = Array();
			}
			$this->_systemMessage[] = Array(
				'type'  => $type,
				'text'  => $text
			);
			return true;
		}
		protected function _setDirectoryPath($dir) {
			if (empty($dir) || $dir == '.') {
				return '.';
			}
			while (strpos($dir, '//')) {
				$dir = str_replace('//', '/', $dir);
			}
			if(substr($dir, -1, 1) == '/') {
				$dir = substr($dir, 0, -1);
			}
			if (!file_exists($dir) || !is_dir($dir)) {
				$this->setSystemMessage('danger', '<b>ERROR:</b> File path does not exist');
				return '.';
			}
			if ($this->_isHidden($dir)) {
				$this->setSystemMessage('danger', '<b>ERROR:</b> Access denied');
				return '.';
			}
			if (strpos($dir, '<') !== false || strpos($dir, '>') !== false
			|| strpos($dir, '..') !== false || strpos($dir, '/') === 0) {
				$this->setSystemMessage('danger', '<b>ERROR:</b> An invalid path string was detected');
				return '.';
			} else {
				$directoryPath = $dir;
			}
			return $directoryPath;
		}
		protected function _readDirectory($directory, $sort = 'natcase') {
			$directoryArray = Array();
			$files = scandir($directory);
			foreach ($files as $file) {
				if ($file != '.') {
					$relativePath = $directory . '/' . $file;
					if (substr($relativePath, 0, 2) == './') {
						$relativePath = substr($relativePath, 2);
					}
					if ($this->_directory == '.' && $file == '..'){
						continue;
					} else {
						$realPath = realpath($relativePath);
						if (is_dir($realPath)) {
							$iconClass = 'folder';
							$sort = 1;
						} else {
							$fileExt = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
							if (isset($this->_fileTypes[$fileExt])) {
								$iconClass = $this->_fileTypes[$fileExt];
							} else {
								$iconClass = $this->_fileTypes['blank'];
							}
							$sort = 2;
						}
					}
					if ($file == '..') {
						if ($this->_directory != '.') {
							$pathArray = explode('/', $relativePath);
							unset($pathArray[count($pathArray)-1]);
							unset($pathArray[count($pathArray)-1]);
							$directoryPath = implode('/', $pathArray);

							if (!empty($directoryPath)) {
								$directoryPath = '?dir=' . rawurlencode($directoryPath);
							}
							$directoryArray['..'] = Array(
								'file_path'  => $this->_appURL . $directoryPath,
								'url_path'   => $this->_appURL . $directoryPath,
								'file_size'  => '-',
								'mod_time'   => date('Y-m-d H:i:s', filemtime($realPath)),
								'icon_class' => 'fa-level-up',
								'sort'       => 0
							);
						}
					} elseif (!$this->_isHidden($relativePath)) {
						if ($this->_directory != '.' || $file != 'index.php') {
							$urlPath = implode('/', array_map('rawurlencode', explode('/', $relativePath)));
							if (is_dir($relativePath)) {
								$urlPath = '?dir=' . $urlPath;
							} else {
								$urlPath = $urlPath;
							}
							$directoryArray[pathinfo($relativePath, PATHINFO_BASENAME)] = Array(
								'file_path'  => $relativePath,
								'url_path'   => $urlPath,
								'file_size'  => is_dir($realPath) ? '-' : $this->getFileSize($realPath),
								'mod_time'   => date('Y-m-d H:i:s', filemtime($realPath)),
								'icon_class' => $iconClass,
								'sort'       => $sort
							);
						}
					}
				}
			}
			$reverseSort = in_array($this->_directory, $this->_config['reverse_sort']);
			$sortedArray = $this->_arraySort($directoryArray, $this->_config['list_sort_order'], $reverseSort);
			return $sortedArray;
		}
		protected function _arraySort($array, $sortMethod, $reverse = false) {
			$sortedArray = Array();
			$finalArray  = Array();
			$keys = array_keys($array);
			switch ($sortMethod) {
				case 'asort':
					asort($keys);
					break;
				case 'arsort':
					arsort($keys);
					break;
				case 'ksort':
					ksort($keys);
					break;
				case 'krsort':
					krsort($keys);
					break;
				case 'natcasesort':
					natcasesort($keys);
					break;
				case 'natsort':
					natsort($keys);
					break;
				case 'shuffle':
					shuffle($keys);
					break;
			}
			if ($this->_config['list_folders_first']) {
				foreach ($keys as $key) {
					if ($array[$key]['sort'] == 0) {
						$sortedArray['0'][$key] = $array[$key];
					}
				}
				foreach ($keys as $key) {
					if ($array[$key]['sort'] == 1) {
						$sortedArray[1][$key] = $array[$key];
					}
				}
				foreach ($keys as $key) {
					if ($array[$key]['sort'] == 2) {
						$sortedArray[2][$key] = $array[$key];
					}
				}
				if ($reverse) {
					$sortedArray[1] = array_reverse($sortedArray[1]);
					$sortedArray[2] = array_reverse($sortedArray[2]);
				}
			} else {
				foreach ($keys as $key) {
					if ($array[$key]['sort'] == 0) {
						$sortedArray[0][$key] = $array[$key];
					}
				}
				foreach ($keys as $key) {
					if ($array[$key]['sort'] > 0) {
						$sortedArray[1][$key] = $array[$key];
					}
				}
				if ($reverse) {
					$sortedArray[1] = array_reverse($sortedArray[1]);
				}
			}
			foreach ($sortedArray as $array) {
				if (empty($array)) continue;
				foreach ($array as $key => $value) {
					$finalArray[$key] = $value;
				}
			}
			return $finalArray;
		}
		protected function _isHidden($filePath) {
			if ($this->_config['hide_dot_files']) {
				$this->_config['hidden_files'] = array_merge(
					$this->_config['hidden_files'],
					Array('.*', '*/.*')
				);
			}
			foreach ($this->_config['hidden_files'] as $hiddenPath) {
				if (fnmatch($hiddenPath, $filePath)) {
					return true;
				}
			}
			return false;
		}
		protected function _getAppUrl() {
			if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
				$protocol = 'https://';
			} else {
				$protocol = 'http://';
			}
			$host = $_SERVER['HTTP_HOST'];
			$pathParts = pathinfo($_SERVER['PHP_SELF']);
			$path      = $pathParts['dirname'];
			if (substr($path, -1) == '\\') {
				$path = substr($path, 0, -1);
			}
			if (substr($path, -1) != '/') {
				$path = $path . '/';
			}
			$appUrl = $protocol . $host . $path;
			return $appUrl;
		}
		protected function _getRelativePath($fromPath, $toPath) {
			if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
			$fromPath = str_replace(DS . DS, DS, $fromPath);
			$toPath = str_replace(DS . DS, DS, $toPath);
			$fromPathArray = explode(DS, $fromPath);
			$toPathArray = explode(DS, $toPath);
			$x = count($fromPathArray) - 1;
			if(!trim($fromPathArray[$x])) {
				array_pop($fromPathArray);
			}
			$x = count($toPathArray) - 1;
			if(!trim($toPathArray[$x])) {
				array_pop($toPathArray);
			}
			$arrayMax = max(count($fromPathArray), count($toPathArray));
			$diffArray = Array();
			$samePath = true;
			$key = 1;
			while ($key <= $arrayMax) {
				$toPath = isset($toPathArray[$key]) ? $toPathArray[$key] : null;
				$fromPath = isset($fromPathArray[$key]) ? $fromPathArray[$key] : null;
				if ($toPath !== $fromPath || $samePath !== true) {
					if (isset($fromPathArray[$key])) {
						array_unshift($diffArray, '..');
					}
					if (isset($toPathArray[$key])) {
						$diffArray[] = $toPathArray[$key];
					}
					$samePath = false;
				}
				$key++;
			}
			$relativePath = implode('/', $diffArray);
			return $relativePath;
		}
	}
    $lister = new DirectoryLister();
    ini_set('open_basedir', getcwd());
    if (isset($_GET['zip'])) {
        $dirArray = $lister->zipDirectory($_GET['zip']);
		exit();
    } else {
		if (isset($_GET['dir'])) {
			if( !$lister->_config[ "index_subfolders" ] ){
				header("Location: ".$_GET['dir']);
				die();
			}else{
				$dirArray = $lister->listDirectory($_GET['dir']);		
			}
		} else {
			$dirArray = $lister->listDirectory('.');
		}
			
	?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?php if ( $lister->_config[ "default_title" ] != Null){ echo $lister->_config[ "default_title" ]; }else{ echo "Directory listing of ".$lister->getListedPath(); } ?></title>
		<link rel="shortcut icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAGrSURBVDjLxZO7ihRBFIa/6u0ZW7GHBUV0UQQTZzd3QdhMQxOfwMRXEANBMNQX0MzAzFAwEzHwARbNFDdwEd31Mj3X7a6uOr9BtzNjYjKBJ6nicP7v3KqcJFaxhBVtZUAK8OHlld2st7Xl3DJPVONP+zEUV4HqL5UDYHr5xvuQAjgl/Qs7TzvOOVAjxjlC+ePSwe6DfbVegLVuT4r14eTr6zvA8xSAoBLzx6pvj4l+DZIezuVkG9fY2H7YRQIMZIBwycmzH1/s3F8AapfIPNF3kQk7+kw9PWBy+IZOdg5Ug3mkAATy/t0usovzGeCUWTjCz0B+Sj0ekfdvkZ3abBv+U4GaCtJ1iEm6ANQJ6fEzrG/engcKw/wXQvEKxSEKQxRGKE7Izt+DSiwBJMUSm71rguMYhQKrBygOIRStf4TiFFRBvbRGKiQLWP29yRSHKBTtfdBmHs0BUpgvtgF4yRFR+NUKi0XZcYjCeCG2smkzLAHkbRBmP0/Uk26O5YnUActBp1GsAI+S5nRJJJal5K1aAMrq0d6Tm9uI6zjyf75dAe6tx/SsWeD//o2/Ab6IH3/h25pOAAAAAElFTkSuQmCC">
		<style>
			body {
				font-family: "Cutive Mono", monospace, serif;
				background: #1C1C1C;
				padding: 70px 0 0;
				color: #777;
			}
			body.breadcrumb-fixed {
				padding-top: 56px;
			}
			.navbar-default{
				background: #212121;
				border-color: black;
			}
			.nav>li>a:focus, .nav>li>a:hover {
				text-decoration: none;
				background: none;
				color: #FFF;
			}
			.container {
				max-width: 960px;
			}
			#page-navbar .navbar-text {
				display: block;
				float: left;
				max-width: 80%;
				overflow: hidden;
				text-overflow: ellipsis;
				white-space: nowrap;
			}
			#directory-list-header {
				font-weight: bold;
				padding: 10px 0;
			}
			#directory-listing li {
				position: relative;
			}
			.file-name .fa-folder{
				color: #57FA62;			
			}
			.file-name {
				overflow: hidden;
				text-overflow: ellipsis;
				white-space: nowrap;
			}
			.file-name i {
				color: #5780FA;
			}
			.file-info-button,
			.web-link-button {
				display: inline-block;
				cursor: pointer;
				margin-left: 100%;
				padding: 6px 10px !important;
				position: absolute !important;
				top: 4px;
			}
			.footer {
				line-height: 50px;
				text-align: center;
			}
			#file-info {
				margin: 0;
			}
			#file-info .table-title {
				font-weight: bold;
				text-align: right;
			}
			.nav > li > a{
				padding: 5px;
			}
			@media (max-width: 767px) {
				.navbar-nav {
					float: left;
					margin: 0;
					padding-bottom: 0;
					padding-top: 0;
				}
				.navbar-nav > li {
					float: left;
				}
				.navbar-nav > li > a {
					padding-bottom: 15px;
					padding-top: 15px;
				}
				.navbar-right {
					float: right !important;
				}
				#page-navbar .navbar-text {
					margin-left: 15px;
					margin-right: 15px;
					max-width: 75%;
				}
				.file-info-button {
					display: none !important;
				}
			}
			html {
				position: relative;
				min-height: 100%;
			}
			body {
				padding: 70px 0;
				font-size: 14px;
				margin: 0;
			}
			a {
				color: #D9D9D9;
				text-decoration: none;
				white-space: nowrap;
			}
			ul {
				list-style: none;
				margin: 0; padding: 0;
			}
			.text-right {
				text-align: right;
			}
			a:focus, a:hover {
				color: #23527c;
				text-decoration: underline;
			}
			.container {
				max-width: 960px;
				margin: auto;
				padding: 0 15px;
			}
			.navbar {
				position: fixed;
				top: 0;
				z-index: 1030;
				background: #212121;
				border-bottom: 1px solid #000;
			}
			.navbar, .footer {
				width: 100%;
				background: #212121;
			}
			.navbar .container,
			.row-flex {
				display: flex;
			}
			.navbar-text {
				color: #777;
				line-height: 20px;
			}
			.navbar-text, 
			.navbar-default .navbar-nav>li>a {
				color: #777;
			}
			.navbar-default .navbar-nav>li>a:focus,
			.navbar-default .navbar-nav>li>a:hover {
				color: #333;
			}
			.navbar-text, .navbar-right {
				flex: 1;
				margin: 15px 0;
			}
			.navbar-right {
				text-align: right;
			}
			.col-7 {
				flex: 7;
			}
			.col-3 {
				flex: 3;
			}
			.col-2 {
				flex: 2;
			}
			#directory-listing.nav-pills a {
				display: block;
			}
			.nav>li>a {
				padding: 5px 0;
				line-height: 20px;
			}
			.footer {
				position: absolute;
				left: 0; right: 0; bottom: 0;
				height: 50px;
				border-top: 1px solid #000;
			}
			.hidden, .hidden-xs {
				display: none;
			}
			.icon-svg {
				height: 15px;
				width: 15px;
				margin: 0 auto;
				position: relative;
				bottom: -2px;
				fill: #777;
			}
			.icon-svg #folder{
				fill: #CCC;
			}
			@media screen and (min-width: 768px)  {
				.col-3.hidden-xs {
					display: block;
				}
			}
			a svg a:focus svg, a:hover svg {
				fill: #444 !important;
			}
			#download-all-link {
				padding: 0;
				height: 18px;
				width: 18px;
				display: inline-block;
				vertical-align: middle;
				fill: #DDD;
			}
			#markdown-content {
				background: #222;
				min-height: 300px;
				padding-left: 20px;
				margin: 5px;
				margin-bottom: 20px;
				border-bottom: 1px solid #000;
				border-top: 1px solid #000;
				position: relative;
				top: 20px;
				color: #999;
			}
			#markdown-content-date{
				padding-top: 5px;
				padding-bottom: 20px;
			}
			/* Markdown styles */
			#markdown-content li{
				padding: 3px;
				padding-left: 0px;
			}

		</style>
		<div class="hidden">
			<svg xmlns="http://www.w3.org/2000/svg">
				<symbol id="cloud-download" viewBox="0 0 1792 1792"><path d="M1216 928q0-14-9-23t-23-9h-224v-352q0-13-9.5-22.5t-22.5-9.5h-192q-13 0-22.5 9.5t-9.5 22.5v352h-224q-13 0-22.5 9.5t-9.5 22.5q0 14 9 23l352 352q9 9 23 9t23-9l351-351q10-12 10-24zm640 224q0 159-112.5 271.5t-271.5 112.5h-1088q-185 0-316.5-131.5t-131.5-316.5q0-130 70-240t188-165q-2-30-2-43 0-212 150-362t362-150q156 0 285.5 87t188.5 231q71-62 166-62 106 0 181 75t75 181q0 76-41 138 130 31 213.5 135.5t83.5 238.5z"/></symbol>
				<symbol id="download" viewBox="0 0 1792 1792"><path d="M1344 1344q0-26-19-45t-45-19-45 19-19 45 19 45 45 19 45-19 19-45zm256 0q0-26-19-45t-45-19-45 19-19 45 19 45 45 19 45-19 19-45zm128-224v320q0 40-28 68t-68 28h-1472q-40 0-68-28t-28-68v-320q0-40 28-68t68-28h465l135 136q58 56 136 56t136-56l136-136h464q40 0 68 28t28 68zm-325-569q17 41-14 70l-448 448q-18 19-45 19t-45-19l-448-448q-31-29-14-70 17-39 59-39h256v-448q0-26 19-45t45-19h256q26 0 45 19t19 45v448h256q42 0 59 39z"/></symbol>
				<symbol id="envelope" viewBox="0 0 1792 1792"><path d="M1792 710v794q0 66-47 113t-113 47h-1472q-66 0-113-47t-47-113v-794q44 49 101 87 362 246 497 345 57 42 92.5 65.5t94.5 48 110 24.5h2q51 0 110-24.5t94.5-48 92.5-65.5q170-123 498-345 57-39 100-87zm0-294q0 79-49 151t-122 123q-376 261-468 325-10 7-42.5 30.5t-54 38-52 32.5-57.5 27-50 9h-2q-23 0-50-9t-57.5-27-52-32.5-54-38-42.5-30.5q-91-64-262-182.5t-205-142.5q-62-42-117-115.5t-55-136.5q0-78 41.5-130t118.5-52h1472q65 0 112.5 47t47.5 113z"/></symbol>
				<symbol id="file-archive-o" viewBox="0 0 1792 1792"><path d="M768 384v-128h-128v128h128zm128 128v-128h-128v128h128zm-128 128v-128h-128v128h128zm128 128v-128h-128v128h128zm700-388q28 28 48 76t20 88v1152q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1600q0-40 28-68t68-28h896q40 0 88 20t76 48zm-444-244v376h376q-10-29-22-41l-313-313q-12-12-41-22zm384 1528v-1024h-416q-40 0-68-28t-28-68v-416h-128v128h-128v-128h-512v1536h1280zm-627-721l107 349q8 27 8 52 0 83-72.5 137.5t-183.5 54.5-183.5-54.5-72.5-137.5q0-25 8-52 21-63 120-396v-128h128v128h79q22 0 39 13t23 34zm-141 465q53 0 90.5-19t37.5-45-37.5-45-90.5-19-90.5 19-37.5 45 37.5 45 90.5 19z"/></symbol>
				<symbol id="file-audio-o" viewBox="0 0 1792 1792"><path d="M1596 380q28 28 48 76t20 88v1152q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1600q0-40 28-68t68-28h896q40 0 88 20t76 48zm-444-244v376h376q-10-29-22-41l-313-313q-12-12-41-22zm384 1528v-1024h-416q-40 0-68-28t-28-68v-416h-768v1536h1280zm-788-814q20 8 20 30v544q0 22-20 30-8 2-12 2-12 0-23-9l-166-167h-131q-14 0-23-9t-9-23v-192q0-14 9-23t23-9h131l166-167q16-15 35-7zm417 689q31 0 50-24 129-159 129-363t-129-363q-16-21-43-24t-47 14q-21 17-23.5 43.5t14.5 47.5q100 123 100 282t-100 282q-17 21-14.5 47.5t23.5 42.5q18 15 40 15zm-211-148q27 0 47-20 87-93 87-219t-87-219q-18-19-45-20t-46 17-20 44.5 18 46.5q52 57 52 131t-52 131q-19 20-18 46.5t20 44.5q20 17 44 17z"/></symbol>
				<symbol id="file-code-o" viewBox="0 0 1792 1792"><path d="M1596 380q28 28 48 76t20 88v1152q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1600q0-40 28-68t68-28h896q40 0 88 20t76 48zm-444-244v376h376q-10-29-22-41l-313-313q-12-12-41-22zm384 1528v-1024h-416q-40 0-68-28t-28-68v-416h-768v1536h1280zm-928-896q8-11 21-12.5t24 6.5l51 38q11 8 12.5 21t-6.5 24l-182 243 182 243q8 11 6.5 24t-12.5 21l-51 38q-11 8-24 6.5t-21-12.5l-226-301q-14-19 0-38zm802 301q14 19 0 38l-226 301q-8 11-21 12.5t-24-6.5l-51-38q-11-8-12.5-21t6.5-24l182-243-182-243q-8-11-6.5-24t12.5-21l51-38q11-8 24-6.5t21 12.5zm-620 461q-13-2-20.5-13t-5.5-24l138-831q2-13 13-20.5t24-5.5l63 10q13 2 20.5 13t5.5 24l-138 831q-2 13-13 20.5t-24 5.5z"/></symbol>
				<symbol id="file-excel-o" viewBox="0 0 1792 1792"><path d="M1596 380q28 28 48 76t20 88v1152q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1600q0-40 28-68t68-28h896q40 0 88 20t76 48zm-444-244v376h376q-10-29-22-41l-313-313q-12-12-41-22zm384 1528v-1024h-416q-40 0-68-28t-28-68v-416h-768v1536h1280zm-979-234v106h281v-106h-75l103-161q5-7 10-16.5t7.5-13.5 3.5-4h2q1 4 5 10 2 4 4.5 7.5t6 8 6.5 8.5l107 161h-76v106h291v-106h-68l-192-273 195-282h67v-107h-279v107h74l-103 159q-4 7-10 16.5t-9 13.5l-2 3h-2q-1-4-5-10-6-11-17-23l-106-159h76v-107h-290v107h68l189 272-194 283h-68z"/></symbol>
				<symbol id="file-image-o" viewBox="0 0 1792 1792"><path d="M1596 380q28 28 48 76t20 88v1152q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1600q0-40 28-68t68-28h896q40 0 88 20t76 48zm-444-244v376h376q-10-29-22-41l-313-313q-12-12-41-22zm384 1528v-1024h-416q-40 0-68-28t-28-68v-416h-768v1536h1280zm-128-448v320h-1024v-192l192-192 128 128 384-384zm-832-192q-80 0-136-56t-56-136 56-136 136-56 136 56 56 136-56 136-136 56z"/></symbol>
				<symbol id="file-movie-o" viewBox="0 0 1792 1792"><path d="M1596 380q28 28 48 76t20 88v1152q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1600q0-40 28-68t68-28h896q40 0 88 20t76 48zm-444-244v376h376q-10-29-22-41l-313-313q-12-12-41-22zm384 1528v-1024h-416q-40 0-68-28t-28-68v-416h-768v1536h1280zm-640-896q52 0 90 38t38 90v384q0 52-38 90t-90 38h-384q-52 0-90-38t-38-90v-384q0-52 38-90t90-38h384zm492 2q20 8 20 30v576q0 22-20 30-8 2-12 2-14 0-23-9l-265-266v-90l265-266q9-9 23-9 4 0 12 2z"/></symbol>
				<symbol id="file-o" viewBox="0 0 1792 1792"><path d="M1596 380q28 28 48 76t20 88v1152q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1600q0-40 28-68t68-28h896q40 0 88 20t76 48zm-444-244v376h376q-10-29-22-41l-313-313q-12-12-41-22zm384 1528v-1024h-416q-40 0-68-28t-28-68v-416h-768v1536h1280z"/></symbol>
				<symbol id="file-pdf-o" viewBox="0 0 1792 1792"><path d="M1596 380q28 28 48 76t20 88v1152q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1600q0-40 28-68t68-28h896q40 0 88 20t76 48zm-444-244v376h376q-10-29-22-41l-313-313q-12-12-41-22zm384 1528v-1024h-416q-40 0-68-28t-28-68v-416h-768v1536h1280zm-514-593q33 26 84 56 59-7 117-7 147 0 177 49 16 22 2 52 0 1-1 2l-2 2v1q-6 38-71 38-48 0-115-20t-130-53q-221 24-392 83-153 262-242 262-15 0-28-7l-24-12q-1-1-6-5-10-10-6-36 9-40 56-91.5t132-96.5q14-9 23 6 2 2 2 4 52-85 107-197 68-136 104-262-24-82-30.5-159.5t6.5-127.5q11-40 42-40h22q23 0 35 15 18 21 9 68-2 6-4 8 1 3 1 8v30q-2 123-14 192 55 164 146 238zm-576 411q52-24 137-158-51 40-87.5 84t-49.5 74zm398-920q-15 42-2 132 1-7 7-44 0-3 7-43 1-4 4-8-1-1-1-2t-.5-1.5-.5-1.5q-1-22-13-36 0 1-1 2v2zm-124 661q135-54 284-81-2-1-13-9.5t-16-13.5q-76-67-127-176-27 86-83 197-30 56-45 83zm646-16q-24-24-140-24 76 28 124 28 14 0 18-1 0-1-2-3z"/></symbol>
				<symbol id="file-photo-o" viewBox="0 0 1792 1792"><path d="M1596 380q28 28 48 76t20 88v1152q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1600q0-40 28-68t68-28h896q40 0 88 20t76 48zm-444-244v376h376q-10-29-22-41l-313-313q-12-12-41-22zm384 1528v-1024h-416q-40 0-68-28t-28-68v-416h-768v1536h1280zm-128-448v320h-1024v-192l192-192 128 128 384-384zm-832-192q-80 0-136-56t-56-136 56-136 136-56 136 56 56 136-56 136-136 56z"/></symbol>
				<symbol id="file-picture-o" viewBox="0 0 1792 1792"><path d="M1596 380q28 28 48 76t20 88v1152q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1600q0-40 28-68t68-28h896q40 0 88 20t76 48zm-444-244v376h376q-10-29-22-41l-313-313q-12-12-41-22zm384 1528v-1024h-416q-40 0-68-28t-28-68v-416h-768v1536h1280zm-128-448v320h-1024v-192l192-192 128 128 384-384zm-832-192q-80 0-136-56t-56-136 56-136 136-56 136 56 56 136-56 136-136 56z"/></symbol>
				<symbol id="file-powerpoint-o" viewBox="0 0 1792 1792"><path d="M1596 380q28 28 48 76t20 88v1152q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1600q0-40 28-68t68-28h896q40 0 88 20t76 48zm-444-244v376h376q-10-29-22-41l-313-313q-12-12-41-22zm384 1528v-1024h-416q-40 0-68-28t-28-68v-416h-768v1536h1280zm-992-234v106h327v-106h-93v-167h137q76 0 118-15 67-23 106.5-87t39.5-146q0-81-37-141t-100-87q-48-19-130-19h-368v107h92v555h-92zm353-280h-119v-268h120q52 0 83 18 56 33 56 115 0 89-62 120-31 15-78 15z"/></symbol>
				<symbol id="file-sound-o" viewBox="0 0 1792 1792"><path d="M1596 380q28 28 48 76t20 88v1152q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1600q0-40 28-68t68-28h896q40 0 88 20t76 48zm-444-244v376h376q-10-29-22-41l-313-313q-12-12-41-22zm384 1528v-1024h-416q-40 0-68-28t-28-68v-416h-768v1536h1280zm-788-814q20 8 20 30v544q0 22-20 30-8 2-12 2-12 0-23-9l-166-167h-131q-14 0-23-9t-9-23v-192q0-14 9-23t23-9h131l166-167q16-15 35-7zm417 689q31 0 50-24 129-159 129-363t-129-363q-16-21-43-24t-47 14q-21 17-23.5 43.5t14.5 47.5q100 123 100 282t-100 282q-17 21-14.5 47.5t23.5 42.5q18 15 40 15zm-211-148q27 0 47-20 87-93 87-219t-87-219q-18-19-45-20t-46 17-20 44.5 18 46.5q52 57 52 131t-52 131q-19 20-18 46.5t20 44.5q20 17 44 17z"/></symbol>
				<symbol id="file-text-o" viewBox="0 0 1792 1792"><path d="M1596 380q28 28 48 76t20 88v1152q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1600q0-40 28-68t68-28h896q40 0 88 20t76 48zm-444-244v376h376q-10-29-22-41l-313-313q-12-12-41-22zm384 1528v-1024h-416q-40 0-68-28t-28-68v-416h-768v1536h1280zm-1024-864q0-14 9-23t23-9h704q14 0 23 9t9 23v64q0 14-9 23t-23 9h-704q-14 0-23-9t-9-23v-64zm736 224q14 0 23 9t9 23v64q0 14-9 23t-23 9h-704q-14 0-23-9t-9-23v-64q0-14 9-23t23-9h704zm0 256q14 0 23 9t9 23v64q0 14-9 23t-23 9h-704q-14 0-23-9t-9-23v-64q0-14 9-23t23-9h704z"/></symbol>
				<symbol id="file-text" viewBox="0 0 1792 1792"><path d="M1596 476q14 14 28 36h-472v-472q22 14 36 28zm-476 164h544v1056q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1600q0-40 28-68t68-28h800v544q0 40 28 68t68 28zm160 736v-64q0-14-9-23t-23-9h-704q-14 0-23 9t-9 23v64q0 14 9 23t23 9h704q14 0 23-9t9-23zm0-256v-64q0-14-9-23t-23-9h-704q-14 0-23 9t-9 23v64q0 14 9 23t23 9h704q14 0 23-9t9-23zm0-256v-64q0-14-9-23t-23-9h-704q-14 0-23 9t-9 23v64q0 14 9 23t23 9h704q14 0 23-9t9-23z"/></symbol>
				<symbol id="file-video-o" viewBox="0 0 1792 1792"><path d="M1596 380q28 28 48 76t20 88v1152q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1600q0-40 28-68t68-28h896q40 0 88 20t76 48zm-444-244v376h376q-10-29-22-41l-313-313q-12-12-41-22zm384 1528v-1024h-416q-40 0-68-28t-28-68v-416h-768v1536h1280zm-640-896q52 0 90 38t38 90v384q0 52-38 90t-90 38h-384q-52 0-90-38t-38-90v-384q0-52 38-90t90-38h384zm492 2q20 8 20 30v576q0 22-20 30-8 2-12 2-14 0-23-9l-265-266v-90l265-266q9-9 23-9 4 0 12 2z"/></symbol>
				<symbol id="file-word-o" viewBox="0 0 1792 1792"><path d="M1596 380q28 28 48 76t20 88v1152q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1600q0-40 28-68t68-28h896q40 0 88 20t76 48zm-444-244v376h376q-10-29-22-41l-313-313q-12-12-41-22zm384 1528v-1024h-416q-40 0-68-28t-28-68v-416h-768v1536h1280zm-1175-896v107h70l164 661h159l128-485q7-20 10-46 2-16 2-24h4l3 24q1 3 3.5 20t5.5 26l128 485h159l164-661h70v-107h-300v107h90l-99 438q-5 20-7 46l-2 21h-4l-3-21q-1-5-4-21t-5-25l-144-545h-114l-144 545q-2 9-4.5 24.5t-3.5 21.5l-4 21h-4l-2-21q-2-26-7-46l-99-438h90v-107h-300z"/></symbol>
				<symbol id="file-zip-o" viewBox="0 0 1792 1792"><path d="M768 384v-128h-128v128h128zm128 128v-128h-128v128h128zm-128 128v-128h-128v128h128zm128 128v-128h-128v128h128zm700-388q28 28 48 76t20 88v1152q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1600q0-40 28-68t68-28h896q40 0 88 20t76 48zm-444-244v376h376q-10-29-22-41l-313-313q-12-12-41-22zm384 1528v-1024h-416q-40 0-68-28t-28-68v-416h-128v128h-128v-128h-512v1536h1280zm-627-721l107 349q8 27 8 52 0 83-72.5 137.5t-183.5 54.5-183.5-54.5-72.5-137.5q0-25 8-52 21-63 120-396v-128h128v128h79q22 0 39 13t23 34zm-141 465q53 0 90.5-19t37.5-45-37.5-45-90.5-19-90.5 19-37.5 45 37.5 45 90.5 19z"/></symbol>
				<symbol id="file" viewBox="0 0 1792 1792"><path d="M1152 512v-472q22 14 36 28l408 408q14 14 28 36h-472zm-128 32q0 40 28 68t68 28h544v1056q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1600q0-40 28-68t68-28h800v544z"/></symbol>
				<symbol id="files-o" viewBox="0 0 1792 1792"><path d="M1696 384q40 0 68 28t28 68v1216q0 40-28 68t-68 28h-960q-40 0-68-28t-28-68v-288h-544q-40 0-68-28t-28-68v-672q0-40 20-88t48-76l408-408q28-28 76-48t88-20h416q40 0 68 28t28 68v328q68-40 128-40h416zm-544 213l-299 299h299v-299zm-640-384l-299 299h299v-299zm196 647l316-316v-416h-384v416q0 40-28 68t-68 28h-416v640h512v-256q0-40 20-88t48-76zm956 804v-1152h-384v416q0 40-28 68t-68 28h-416v640h896z"/></symbol>
				<symbol id="floppy-o" viewBox="0 0 1792 1792"><path d="M512 1536h768v-384h-768v384zm896 0h128v-896q0-14-10-38.5t-20-34.5l-281-281q-10-10-34-20t-39-10v416q0 40-28 68t-68 28h-576q-40 0-68-28t-28-68v-416h-128v1280h128v-416q0-40 28-68t68-28h832q40 0 68 28t28 68v416zm-384-928v-320q0-13-9.5-22.5t-22.5-9.5h-192q-13 0-22.5 9.5t-9.5 22.5v320q0 13 9.5 22.5t22.5 9.5h192q13 0 22.5-9.5t9.5-22.5zm640 32v928q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1344q0-40 28-68t68-28h928q40 0 88 20t76 48l280 280q28 28 48 76t20 88z"/></symbol>
				<symbol id="folder-open" viewBox="0 0 1792 1792"><path d="M1815 952q0 31-31 66l-336 396q-43 51-120.5 86.5t-143.5 35.5h-1088q-34 0-60.5-13t-26.5-43q0-31 31-66l336-396q43-51 120.5-86.5t143.5-35.5h1088q34 0 60.5 13t26.5 43zm-343-344v160h-832q-94 0-197 47.5t-164 119.5l-337 396-5 6q0-4-.5-12.5t-.5-12.5v-960q0-92 66-158t158-66h320q92 0 158 66t66 158v32h544q92 0 158 66t66 158z"/></symbol>
				<symbol id="folder" viewBox="0 0 1792 1792"><path d="M1728 608v704q0 92-66 158t-158 66h-1216q-92 0-158-66t-66-158v-960q0-92 66-158t158-66h320q92 0 158 66t66 158v32h672q92 0 158 66t66 158z"/></symbol>
				<symbol id="font" viewBox="0 0 1792 1792"><path d="M789 559l-170 450q33 0 136.5 2t160.5 2q19 0 57-2-87-253-184-452zm-725 1105l2-79q23-7 56-12.5t57-10.5 49.5-14.5 44.5-29 31-50.5l237-616 280-724h128q8 14 11 21l205 480q33 78 106 257.5t114 274.5q15 34 58 144.5t72 168.5q20 45 35 57 19 15 88 29.5t84 20.5q6 38 6 57 0 4-.5 13t-.5 13q-63 0-190-8t-191-8q-76 0-215 7t-178 8q0-43 4-78l131-28q1 0 12.5-2.5t15.5-3.5 14.5-4.5 15-6.5 11-8 9-11 2.5-14q0-16-31-96.5t-72-177.5-42-100l-450-2q-26 58-76.5 195.5t-50.5 162.5q0 22 14 37.5t43.5 24.5 48.5 13.5 57 8.5 41 4q1 19 1 58 0 9-2 27-58 0-174.5-10t-174.5-10q-8 0-26.5 4t-21.5 4q-80 14-188 14z"/></symbol>
				<symbol id="gear" viewBox="0 0 1792 1792"><path d="M1152 896q0-106-75-181t-181-75-181 75-75 181 75 181 181 75 181-75 75-181zm512-109v222q0 12-8 23t-20 13l-185 28q-19 54-39 91 35 50 107 138 10 12 10 25t-9 23q-27 37-99 108t-94 71q-12 0-26-9l-138-108q-44 23-91 38-16 136-29 186-7 28-36 28h-222q-14 0-24.5-8.5t-11.5-21.5l-28-184q-49-16-90-37l-141 107q-10 9-25 9-14 0-25-11-126-114-165-168-7-10-7-23 0-12 8-23 15-21 51-66.5t54-70.5q-27-50-41-99l-183-27q-13-2-21-12.5t-8-23.5v-222q0-12 8-23t19-13l186-28q14-46 39-92-40-57-107-138-10-12-10-24 0-10 9-23 26-36 98.5-107.5t94.5-71.5q13 0 26 10l138 107q44-23 91-38 16-136 29-186 7-28 36-28h222q14 0 24.5 8.5t11.5 21.5l28 184q49 16 90 37l142-107q9-9 24-9 13 0 25 10 129 119 165 170 7 8 7 22 0 12-8 23-15 21-51 66.5t-54 70.5q26 50 41 98l183 28q13 2 21 12.5t8 23.5z"/></symbol>
				<symbol id="gears" viewBox="0 0 1792 1792"><path d="M832 896q0-106-75-181t-181-75-181 75-75 181 75 181 181 75 181-75 75-181zm768 512q0-52-38-90t-90-38-90 38-38 90q0 53 37.5 90.5t90.5 37.5 90.5-37.5 37.5-90.5zm0-1024q0-52-38-90t-90-38-90 38-38 90q0 53 37.5 90.5t90.5 37.5 90.5-37.5 37.5-90.5zm-384 421v185q0 10-7 19.5t-16 10.5l-155 24q-11 35-32 76 34 48 90 115 7 10 7 20 0 12-7 19-23 30-82.5 89.5t-78.5 59.5q-11 0-21-7l-115-90q-37 19-77 31-11 108-23 155-7 24-30 24h-186q-11 0-20-7.5t-10-17.5l-23-153q-34-10-75-31l-118 89q-7 7-20 7-11 0-21-8-144-133-144-160 0-9 7-19 10-14 41-53t47-61q-23-44-35-82l-152-24q-10-1-17-9.5t-7-19.5v-185q0-10 7-19.5t16-10.5l155-24q11-35 32-76-34-48-90-115-7-11-7-20 0-12 7-20 22-30 82-89t79-59q11 0 21 7l115 90q34-18 77-32 11-108 23-154 7-24 30-24h186q11 0 20 7.5t10 17.5l23 153q34 10 75 31l118-89q8-7 20-7 11 0 21 8 144 133 144 160 0 9-7 19-12 16-42 54t-45 60q23 48 34 82l152 23q10 2 17 10.5t7 19.5zm640 533v140q0 16-149 31-12 27-30 52 51 113 51 138 0 4-4 7-122 71-124 71-8 0-46-47t-52-68q-20 2-30 2t-30-2q-14 21-52 68t-46 47q-2 0-124-71-4-3-4-7 0-25 51-138-18-25-30-52-149-15-149-31v-140q0-16 149-31 13-29 30-52-51-113-51-138 0-4 4-7 4-2 35-20t59-34 30-16q8 0 46 46.5t52 67.5q20-2 30-2t30 2q51-71 92-112l6-2q4 0 124 70 4 3 4 7 0 25-51 138 17 23 30 52 149 15 149 31zm0-1024v140q0 16-149 31-12 27-30 52 51 113 51 138 0 4-4 7-122 71-124 71-8 0-46-47t-52-68q-20 2-30 2t-30-2q-14 21-52 68t-46 47q-2 0-124-71-4-3-4-7 0-25 51-138-18-25-30-52-149-15-149-31v-140q0-16 149-31 13-29 30-52-51-113-51-138 0-4 4-7 4-2 35-20t59-34 30-16q8 0 46 46.5t52 67.5q20-2 30-2t30 2q51-71 92-112l6-2q4 0 124 70 4 3 4 7 0 25-51 138 17 23 30 52 149 15 149 31z"/></symbol>
				<symbol id="hdd-o" viewBox="0 0 1792 1792"><path d="M1168 1216q0 33-23.5 56.5t-56.5 23.5-56.5-23.5-23.5-56.5 23.5-56.5 56.5-23.5 56.5 23.5 23.5 56.5zm256 0q0 33-23.5 56.5t-56.5 23.5-56.5-23.5-23.5-56.5 23.5-56.5 56.5-23.5 56.5 23.5 23.5 56.5zm112 160v-320q0-13-9.5-22.5t-22.5-9.5h-1216q-13 0-22.5 9.5t-9.5 22.5v320q0 13 9.5 22.5t22.5 9.5h1216q13 0 22.5-9.5t9.5-22.5zm-1230-480h1180l-157-482q-4-13-16-21.5t-26-8.5h-782q-14 0-26 8.5t-16 21.5zm1358 160v320q0 66-47 113t-113 47h-1216q-66 0-113-47t-47-113v-320q0-25 16-75l197-606q17-53 63-86t101-33h782q55 0 101 33t63 86l197 606q16 50 16 75z"/></symbol>
				<symbol id="music" viewBox="0 0 1792 1792"><path d="M1664 224v1120q0 50-34 89t-86 60.5-103.5 32-96.5 10.5-96.5-10.5-103.5-32-86-60.5-34-89 34-89 86-60.5 103.5-32 96.5-10.5q105 0 192 39v-537l-768 237v709q0 50-34 89t-86 60.5-103.5 32-96.5 10.5-96.5-10.5-103.5-32-86-60.5-34-89 34-89 86-60.5 103.5-32 96.5-10.5q105 0 192 39v-967q0-31 19-56.5t49-35.5l832-256q12-4 28-4 40 0 68 28t28 68z"/></symbol>
				<symbol id="terminal" viewBox="0 0 1792 1792"><path d="M649 983l-466 466q-10 10-23 10t-23-10l-50-50q-10-10-10-23t10-23l393-393-393-393q-10-10-10-23t10-23l50-50q10-10 23-10t23 10l466 466q10 10 10 23t-10 23zm1079 457v64q0 14-9 23t-23 9h-960q-14 0-23-9t-9-23v-64q0-14 9-23t23-9h960q14 0 23 9t9 23z"/></symbol>
				<symbol id="youtube-play" viewBox="0 0 1792 1792"><path d="M1280 896q0-37-30-54l-512-320q-31-20-65-2-33 18-33 56v640q0 38 33 56 16 8 31 8 20 0 34-10l512-320q30-17 30-54zm512 0q0 96-1 150t-8.5 136.5-22.5 147.5q-16 73-69 123t-124 58q-222 25-671 25t-671-25q-71-8-124.5-58t-69.5-123q-14-65-21.5-147.5t-8.5-136.5-1-150 1-150 8.5-136.5 22.5-147.5q16-73 69-123t124-58q222-25 671-25t671 25q71 8 124.5 58t69.5 123q14 65 21.5 147.5t8.5 136.5 1 150z"/></symbol>
			</svg>
		</div>
	</head>


	<body>
		<header id="page-navbar" class="navbar">
			<div class="container">
				<?php $breadcrumbs = $lister->listBreadcrumbs(); ?>
				<p class="navbar-text">
					<?php foreach($breadcrumbs as $breadcrumb): ?>
						<?php if ($breadcrumb != end($breadcrumbs)): ?>
							<a href="<?php echo $breadcrumb["link"]; ?>"><?php echo $breadcrumb["text"]; ?></a>
							<span class="divider">/</span>
						<?php else: ?>
							<?php echo $breadcrumb["text"]; ?>
						<?php endif; ?>
					<?php endforeach; ?>
				</p>
				<div class="navbar-right">
				<?php  if ($lister->isZipEnabled()): ?>
					<a href="?zip=.">
						<svg class="icon-svg" id="download-all-link"><use xlink:href="#download" /></svg>
					</a>
				<?php endif; ?>
				</div>
			</div>
		</header>

		<div id="page-content" class="container">
			<?php if($lister->getSystemMessages()): ?>
				<?php foreach ($lister->getSystemMessages() as $message): ?>
					<div class="alert alert-<?php echo $message["type"]; ?>">
						<?php echo $message["text"]; ?>
						<a class="close" data-dismiss="alert" href="#">&times;</a>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
			<div class="row-flex" id="directory-list-header">
				<div class="col-7">File</div>
				<div class="col-2 text-right">Size</div>
				<div class="col-3 text-right hidden-xs">Last Modified</div>
			</div>
			<ul id="directory-listing" class="nav nav-pills nav-stacked">
				<?php foreach($dirArray as $name => $fileInfo): ?>
					<li data-name="<?php echo $name; ?>" data-href="<?php echo $fileInfo["url_path"]; ?>">
						<a href="<?php echo $fileInfo["url_path"]; ?>" data-name="<?php echo $name; ?>">
							<div class="row-flex">
								<span class="file-name col-7">
									<svg class="icon-svg" id="<?php echo $fileInfo["icon_class"]; ?>"><use xlink:href="#<?php echo $fileInfo["icon_class"]; ?>" /></svg>
									<?php echo $name; ?>
								</span>
								<span class="file-size col-2 text-right">
									<?php echo $fileInfo["file_size"]; ?>
								</span>
								<span class="file-modified col-3 text-right hidden-xs">
									<?php echo $fileInfo["mod_time"]; ?>
								</span>
							</div>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if( file_exists( "index.md" ) ): ?>
				<div id="markdown-content">
					<?php
						$Parsedown = new Parsedown();
						@print( $Parsedown->text( fread( fopen( "index.md", "r" ), filesize( "index.md" ) ) ) );
					?>
				</div>
				<i><center><div id="markdown-content-date"><?php echo date('Y-m-d H:i:s', filemtime( "index.md" ) ); ?></div></center></i>
			<?php endif; ?>
		</div>
		<footer class="footer">
			Powered by <a href="https://github.com/ttyk/OneFileDirectoryLister">One-File Directory Lister</a>
		</footer>
		<?php }; ?>
	</body>
<html>
