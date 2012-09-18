<?php
/*
 Naked: Naked and easy!
 */

// Standard inclusions
import('@.ORG.pChart.lib.pData');
import('@.ORG.pChart.lib.pChart');
class MyChart {
	public function createChart() {
		// Dataset definition
		$DataSet = new pData;
		$DataSet -> AddPoint(array(1, 4, 3, 2, 3, 3, 2, 1, 0, 7, 4, 3, 2, 3, 3, 5, 1, 0, 7));
		$DataSet -> AddSerie();
		$DataSet -> SetSerieName("Sample data", "Serie1");

		// Initialise the graph
		$Test = new pChart(700, 230);
		$Test -> setFontProperties("pChart/Fonts/tahoma.ttf", 10);
		$Test -> setGraphArea(40, 30, 680, 200);
		$Test -> drawGraphArea(252, 252, 252, TRUE);
		 Log::write("dffdf"); 
		$Test -> drawScale($DataSet -> GetData(), $DataSet -> GetDataDescription(), SCALE_NORMAL, 150, 150, 150, TRUE, 0, 2);
		 Log::write("erer"); 
		$Test -> drawGrid(4, TRUE, 230, 230, 230, 70);
		
		// Draw the line graph
		$Test -> drawLineGraph($DataSet -> GetData(), $DataSet -> GetDataDescription());
		$Test -> drawPlotGraph($DataSet -> GetData(), $DataSet -> GetDataDescription(), 3, 2, 255, 255, 255);

		// Finish the graph
		$Test -> setFontProperties("pChart/Fonts/tahoma.ttf", 8);
		$Test -> drawLegend(45, 35, $DataSet -> GetDataDescription(), 255, 255, 255);
		$Test -> setFontProperties("pChart/Fonts/tahoma.ttf", 10);
		$Test -> drawTitle(60, 22, "My pretty graph", 50, 50, 50, 585);
		$Test -> Render("Naked.png") ;
	}
}
?>