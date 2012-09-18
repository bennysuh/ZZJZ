<?php

 // Standard inclusions   
import('@.ORG.pChart.lib.pData');
import('@.ORG.pChart.lib.pChart');
class Chart 
{
	/**
	 +----------------------------------------------------------
	 * 生成折线统计图
	 +----------------------------------------------------------
	 * @access public
	 * @param $xData X轴数据 array
	 * @param $yData Y轴数据 array
	 * @param $title 标题
	 * @param $exportName 生成图片名
	 +----------------------------------------------------------
	 */
	public function createChart($xData,$yData,$title,$exportName)
	{
		 // Dataset definition 定义数据集合
		 $DataSet = new pData;
		 $DataSet->AddPoint($yData,"Serie1");
		 $DataSet->AddPoint($xData,"Serie2");
		 $DataSet->AddSerie("Serie1");
		 $DataSet->SetAbsciseLabelSerie("Serie2");
		 //设置折线的标题
		  $DataSet->SetSerieName("指导价格","Serie1");
		 //设置Y轴坐标单元
		 $DataSet->SetYAxisUnit("元");
		 //设置Y轴标题
		 $DataSet->SetYAxisName("薪资");
		 //节点图片
		 $DataSet->SetSerieSymbol("Serie1","Public/style/img/Point_Asterisk.gif");
		 // Initialise the graph
		 $Test = new pChart(700,230);
		 $Test->setFontProperties("Public/style/Fonts/simfang.TTF",10);
		 $Test->setGraphArea(80,30,670,200);//设置图表位置
		 $Test->drawGraphArea(252,252,252,TRUE);

		 $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2);

		 $Test->drawGrid(4,TRUE,230,230,230,70);
		 // Draw the line graph
		 $Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());//画折线 drawCubicCurve画曲线
		 $Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);//折线上的节点
		 // Finish the graph
		 $Test->setFontProperties("Public/style/Fonts/simfang.TTF",8);
		 $Test->drawLegend(45,35,$DataSet->GetDataDescription(),255,255,255);
		 $Test->setFontProperties("Public/style/Fonts/simfang.TTF",10);
		 $Test->drawTitle(60,22,$title,50,50,50,585);
		 $Test->Render("$exportName");
	}
}

?>