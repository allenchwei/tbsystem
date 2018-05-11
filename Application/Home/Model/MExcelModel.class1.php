<?php 
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @gzy  20150320  Excel
// +----------------------------------------------------------------------
class MExcelModel extends Model {
	
	/* 导出Excel
	 * $expTitle 文件名称
	 * $expCellName   array(array('id','账号序列'),array('username','名字'));
	 * $expTableData  数据
	*/
	public function exportExcel($expTitle,$expCellName,$expTableData){
		/* error_reporting(0);
        header ( "Content-type:application/vnd.ms-excel" );
		header ( "Content-Disposition:filename=" . iconv ( "UTF-8", "GBK", "topic" ) . ".csv" );
		
        //连接数据库   
        $link = mysql_connect('localhost','root','root') or die('连接错误');  
		//选择数据库  
		mysql_select_db("bbs",$link);  
		//设置字符集  
		mysql_query("set names utf8");  
		//查询函数  
		function get_res($sql,$link){ 			  
			$res  = mysql_query($sql,$link);  			  
			if(!$res){  				  
				die("操作失败".mysql_error());  
			}  
			$arr=array();  
			while ($row = mysql_fetch_assoc($res)) {                
				$arr[]=$row;  
			}  
			return $arr;  
		}  
        //查询记录总数  
		function getTotalCount(){
			$result = mysql_query("SELECT count(*) as count FROM medsci_edu_public_medsciedu_topic", $link);                    
			return $result['count'];  
		}  
		
		// 打开PHP文件句柄，php://output 表示直接输出到浏览器  
		$fp = fopen('php://output', 'a');   
		//表头  
        $column_name = array('topic_id','cat_id','user_id','is_best','is_top','topic_title','topic_content','topic_img','hits','total_reply_count','created_time','last_updated_time','topic_status','last_reply_name');  
		// 将中文标题转换编码，否则乱码  
		foreach ($column_name as $i => $v) {    
		   $column_name[$i] = iconv('utf-8', 'gbk', $v);    
		}  
        // 将标题名称通过fputcsv写到文件句柄    
        fputcsv($fp, $column_name);  
        $pagecount = 10000;//一次读取多少条  
        $totalcount = getTotalCount();//总记录数  
        $sql = "select * from medsci_edu_public_medsciedu_topic";  
        for ($i=0;$i<intval($totalcount/$pagecount)+1;$i++){  
            $data = get_res($sql." limit ".strval($i*$pagecount).",{$pagecount}",$link);  
            foreach ( $data as $item ) {  
                $rows = array();  
                foreach ( $item as $v){  
                    $rows[] = iconv('utf-8', 'GBK', $v);  
                }  
                fputcsv($fp, $rows);  
            }  
            // 将已经写到csv中的数据存储变量销毁，释放内存占用  
            unset($data);  
            //刷新缓冲区  
            ob_flush();  
            flush();  
        }  
		exit;   */
		
		
		
		/* set_time_limit(0);		
		$fp = fopen('php://output', 'a');
		
		//输出Excel列名信息
		$head = array();
		foreach ($expCellName as $i => $v) {
			//CSV的Excel支持GBK编码，一定要转换，否则乱码
			$head[$i] = iconv('utf-8', 'gbk', $v);
		}
		
		//将数据通过fputcsv写到文件句柄
		fputcsv($fp, $head);
		
		//计数器
		$cnt = 0;
		//每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
		$limit = 2000;
		
		//逐行取出数据，不浪费内存
		$count = count($expTableData);
		
		for($t=0;$t<$count;$t++) {
			$cnt ++;
			if($limit == $cnt) { //刷新一下输出buffer，防止由于数据过多造成问题
				ob_flush();
				flush();
				$cnt = 0;
			}
			$row[] = $expTableData[$t];
			foreach ($row as $i => $v) {
				$row[$i] = iconv('utf-8', 'gbk', $v);
			}
			fputcsv($fp, $row);
			unset($row);
		} */
		
		/* 	
		set_time_limit(0);
		$expTitle = $expTitle.'_'.date('His');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$expTitle.'.csv"');
		header('Cache-Control: max-age=0');
		header('Cache-Control: max-age=1');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0
		
		//标题
		$title = array();
		foreach($expCellName as $val){
			$title[] = $val['1'];
		}
		
		ob_flush();
		flush();

		//创建临时存储内存
		$fp = fopen('php://memory','w');
		fputcsv($fp,$title,',');
		foreach($expTableData as $item) {
			fputcsv($fp,array_values($item),',');
		}

		rewind($fp);
		$content = "";
		while(!feof($fp)){
			$content .= fread($fp,1024);
		}
		fclose($fp);
		$content = iconv('utf-8','gbk',$content);//转成gbk，否则excel打开乱码
		echo $content;
		exit; */
		
	/* 	set_time_limit(0);
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
		$fileName = iconv('utf-8', 'gb2312', $expTitle.date('_His'));//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        vendor("PHPExcel180.PHPExcel");
       
        $objPHPExcel = new \PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        
        //$objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
		//$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle);  
        for($i=0;$i<$cellNum;$i++){
			//自动设置单元格宽度
			$objPHPExcel->getActiveSheet(0)->getColumnDimension($cellName[$i])->setWidth(18);
			//设置单元格为长数字时也可以显示
			$objPHPExcel->getActiveSheet(0)->getStyle($cellName[$i])->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER); 
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName[$i][1]); 
        } 
        // Miscellaneous glyphs, UTF-8   
        for($i=0;$i<$dataNum;$i++){			
			for($j=0;$j<$cellNum;$j++){
				//$objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+2), $expTableData[$i][$expCellName[$j][0]]);
				//设置单元格以字符串输出
				$objPHPExcel->getActiveSheet()->setCellValueExplicit($cellName[$j].($i+2), $expTableData[$i][$expCellName[$j][0]],\PHPExcel_Cell_DataType::TYPE_STRING);
			}             
        }        
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
        $objWriter->save('php://output'); 
        exit; */
    }	
	
	
	
	
	
	/* 分公司平台收益 - 导出Excel
	 * $expTitle 文件名称
	 * $expCellName   array(array('id','账号序列'),array('username','名字'));
	 * $expTableData  数据
	*/
	public function BexportExcel($expTitle,$expCellName1,$expCellName2,$expCellName3,$expCellName4,$expTableData){
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
		$fileName = iconv('utf-8', 'gb2312', $expTitle.date('_His'));//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum1 = count($expCellName1);
        $cellNum2 = count($expCellName2);
        $cellNum3 = count($expCellName3);
        $cellNum4 = count($expCellName4);
        $dataNum  = count($expTableData);
        vendor("PHPExcel180.PHPExcel");
       
        $objPHPExcel = new \PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        //第一排
		for($i=0;$i<$cellNum1;$i++){
			//自动设置单元格宽度
			$objPHPExcel->getActiveSheet(0)->getColumnDimension($cellName[$i])->setWidth(24);
			//设置单元格为长数字时也可以显示
			$objPHPExcel->getActiveSheet(0)->getStyle($cellName[$i])->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER); 
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName1[$i][1]); 
        }
		 //第二排
		for($i=0;$i<$cellNum2;$i++){
			//自动设置单元格宽度
			$objPHPExcel->getActiveSheet(0)->getColumnDimension($cellName[$i])->setWidth(24);
			//设置单元格为长数字时也可以显示
			$objPHPExcel->getActiveSheet(0)->getStyle($cellName[$i])->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER); 
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName2[$i][1]); 
        }
		//第三排	
		for($i=0;$i<$cellNum3;$i++){
			//自动设置单元格宽度
			$objPHPExcel->getActiveSheet(0)->getColumnDimension($cellName[$i])->setWidth(24);
			//设置单元格为长数字时也可以显示
			$objPHPExcel->getActiveSheet(0)->getStyle($cellName[$i])->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER); 
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'3', $expCellName3[$i][1]); 
        }
		//第四排
        for($i=0;$i<$cellNum4;$i++){
			//自动设置单元格宽度
			$objPHPExcel->getActiveSheet(0)->getColumnDimension($cellName[$i])->setWidth(24);
			//设置单元格为长数字时也可以显示
			$objPHPExcel->getActiveSheet(0)->getStyle($cellName[$i])->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER); 
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'4', $expCellName4[$i][1]); 
        } 
        // Miscellaneous glyphs, UTF-8   
        for($i=0;$i<$dataNum;$i++){			
			for($j=0;$j<$cellNum4;$j++){
				//$objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+2), $expTableData[$i][$expCellName[$j][0]]);
				//设置单元格以字符串输出
				$objPHPExcel->getActiveSheet()->setCellValueExplicit($cellName[$j].($i+5), $expTableData[$i][$expCellName4[$j][0]],\PHPExcel_Cell_DataType::TYPE_STRING);
			}             
        }
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
        $objWriter->save('php://output'); 
        exit;   
    }	

    /**
	 * Import Excel
	 * @param  [array] $file [upload file $_FILES]
	 * @return [array]       [error:array("error","message"); right:array("error"=>1,"data"=>[])]
	*/
    public function importExecl($file){ 
        if(!file_exists($file)){ 
            return array("error"=>0,'message'=>'file not found!');
        } 
        
        vendor("PHPExcel180.PHPExcel");
        $objReader = \PHPExcel_IOFactory::createReader('Excel5'); 
        try{
            $PHPReader = $objReader->load($file);
        }catch(Exception $e){}
        if(!isset($PHPReader)) return array("error"=>0,'message'=>'read error!');
        $allWorksheets = $PHPReader->getAllSheets();
        $i = 0;
        foreach($allWorksheets as $objWorksheet){
            $sheetname=$objWorksheet->getTitle();
            $allRow = $objWorksheet->getHighestRow();//how many rows
            $highestColumn = $objWorksheet->getHighestColumn();//how many columns
            $allColumn = \PHPExcel_Cell::columnIndexFromString($highestColumn);
            $array[$i]["Title"] = $sheetname; 
            $array[$i]["Cols"] = $allColumn; 
            $array[$i]["Rows"] = $allRow; 
            $arr = array();
            $isMergeCell = array();
            foreach ($objWorksheet->getMergeCells() as $cells) {//merge cells
                foreach (\PHPExcel_Cell::extractAllCellReferencesInRange($cells) as $cellReference) {
                    $isMergeCell[$cellReference] = true;
                }
            }
            for($currentRow = 1 ;$currentRow<=$allRow;$currentRow++){ 
                $row = array(); 
                for($currentColumn=0;$currentColumn<$allColumn;$currentColumn++){;                
                    $cell =$objWorksheet->getCellByColumnAndRow($currentColumn, $currentRow);
                    $afCol = \PHPExcel_Cell::stringFromColumnIndex($currentColumn+1);
                    $bfCol = \PHPExcel_Cell::stringFromColumnIndex($currentColumn-1);
                    $col = \PHPExcel_Cell::stringFromColumnIndex($currentColumn);
                    $address = $col.$currentRow;
                    $value = $objWorksheet->getCell($address)->getValue();
                    if(substr($value,0,1)=='='){
                        return array("error"=>0,'message'=>'can not use the formula!');
                        exit;
                    }
                    if($isMergeCell[$col.$currentRow]&&$isMergeCell[$afCol.$currentRow]&&!empty($value)){
                        $temp = $value;
                    }elseif($isMergeCell[$col.$currentRow]&&$isMergeCell[$col.($currentRow-1)]&&empty($value)){
                        $value=$arr[$currentRow-1][$currentColumn];
                    }elseif($isMergeCell[$col.$currentRow]&&$isMergeCell[$bfCol.$currentRow]&&empty($value)){
                        $value=$temp;
                    }
                    $row[$currentColumn] = $value; 
                } 
                $arr[$currentRow] = $row; 
            } 
            $array[$i]["Content"] = $arr; 
            $i++;
        } 
        unset($objWorksheet); 
        unset($PHPReader); 
        unset($PHPExcel); 
        //unlink($file); 
        return array("error"=>1,"data"=>$array); 
    }
}