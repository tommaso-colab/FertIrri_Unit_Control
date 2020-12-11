<?php
// Report all errors
// error_reporting(E_ALL);
// tommaso 21/07/2020 
// automazione IF to SAPIR2: lancio lo script ogni giorno alle 12.00 che sono sicuro
// che IF ha ricevuto il dato meteo da ARPAE (anche se nella prova s3o mando i dato 
// da davis alle 03.00). Faccio estrazione da IF ogni giorno se non ho IRRIGAZIONE quel giorno comunque
// modifico il programma su talgil dicendo che quel giorno ho NO irrigazione. Se invece 
// devo irrigate faccio modifica lista programmazione irrigazione mettendo irr per giorno oggi
// e poi modifico ora inizio ed ora fine (ora inizio per ora mettiamo sempre ore 16.00)
include_once('dll_APIrest_IRRIFRAME.php');
include_once('dll_APIrest_RemoteIRRI_Unit.php');
include_once('secretkey_IF.php');
global $tokenPlotALTAVIA;
$tokenPlotALTAVIA = $IF_Key['token']; // imposta TOKEN
/////////////////PROCESS INPUT
// $todayFormat_irriframe = date("d/m/Y"); //GIORNO DI RUN
$todayFormat_irriframe = date("Y-m-d");
$UnicOra_di_InizioIRRIGAT = 16; // orario Start Irrigazione in Centralina
//////////////////////////////////////////////////////
//////////plot 121179/////////////////////////////////
//////////////////////////////////////////////////////
// input
//IF input
$idplot = ""; //id PLOT IF 
$idUser = ""; //id USER IF
//IRRIUNIT ABSOLUTE irrigation UNIT input
$idTALGIL_unit = ""; // id unit della centralina
$tokenTALGIL = ""; // token per apicall TLG-API-Key
$ID_talgil_Program = 2; // id programma corrispondente ad ID COMBINAZIONE
$ID_sequenzaDelPROGRAMMA = 1; // id della sequenza del programma 
$Start_dateForCycle14d = date("Y-m-d", strtotime("2020-06-28")); // la programmazione della centralina viene fatta a cicli di 14 giorni con un verttore di 1 e 0 mettendo 1 per il giorno in cui deve irrigare: qui si definisce la data in cui iniziare a contare 
$oradigiornoInizioIrr_inMinuti = 60 * $UnicOra_di_InizioIRRIGAT; // partenza irri sempre alle 16.00 !!importante!! ORARIO FINE lo calcola da ORA START quindi se ho tempo IRRIGAZIONE LUNGO RICORDARSI CHE non può SUPERARE le 24 ORE
/////////////////MAIN
//get IF wb result
$Ini_GetWBResult = new getIrrigationAmount ($idUser, $idplot, $todayFormat_irriframe);
$IrrTime = ($Ini_GetWBResult->ParseGETWBres_toIRRIAmount());
echo "</br><strong>Irr_time somma min: </strong>".$IrrTime  ;
//send VALUE TO TALGIL
$Ini_sendTotalgilProcess = new sendTo_Talgil ($idTALGIL_unit, $tokenTALGIL, $ID_talgil_Program, $IrrTime,$Start_dateForCycle14d, $todayFormat_irriframe, $oradigiornoInizioIrr_inMinuti, $ID_sequenzaDelPROGRAMMA);
$VectorToTalgil_DailyProgramSEND = ($Ini_sendTotalgilProcess->Send_To_Talgil_Values());
echo "</br><strong>response talgil: </strong>".$VectorToTalgil_DailyProgramSEND ;
// if today to irrigate create IRRIGATION in IF register 
if ($IrrTime != 'NoIrr'){
					$pullIrri = new pushToIrriframe_IrrigationAmount($idUser, $idplot, $todayFormat_irriframe, "", $IrrTime);
					$ResponsePUSHIrrTO_IRRIFRAME = ($pullIrri->GetPushToIrriframeIRRI());
					echo "<strong>CICLO response to IF UPLOAD:</strong> ".htmlspecialchars($ResponsePUSHIrrTO_IRRIFRAME)."</br>";
}

?>