<?php

/*=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        � full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/*===========================================================================
	questionnaire.inc.php
	@last update: 17-4-2006 by Costas Tsibanis
	@authors list: Dionysios G. Synodinos <synodinos@gmail.com>
==============================================================================        
        @Description: Questionnaire tool tranlation
==============================================================================
*/


$langTestEcho = "������� �� echo";
$langSurveysActive = "������� ������� ���������� ������";
$langSurveysInactive = "������� ������� ���������� ������";
$langSurveyName = "�����";
$langSurveyNumAnswers = "����������";
$langSurveyCreation = "����������";
$langSurveyStart = "������";
$langSurveyEnd = "����";
$langSurveyOperations = "�����������";
$langSurveyEdit = "�����������";
$langSurveyRemove = "��������";
$langSurveyCreate = "����������";
$langSurveyQuestion = "�������";
$langSurveyAnswer = "��������";
$langSurveyAddAnswer = "�������� ����������";
$langSurveyType = "�����";
$langSurveyMC = "��������� ��������";
$langSurveyFillText = "����������� �� ����";
$langSurveyContinue = "��������";
$langSurveyMoreAnswers = "�� ����� ����������";
$langSurveyYes = "���";
$langSurveyNo = "���";
$langSurveyMoreAnswers ="�� ����� ����������";
$langSurveyMoreQuestions = "�� ����� ���������";
$langSurveyCreate = "���������� ������� ���������� ������";
$langSurveyCreated ="� ������ ���������� ������ ������������� �� ��������. ������� <a href=\"survey.php\">���</a> ��� �� ������������ ��� ������ ��� ������� ���������� ������.";
$langSurveyCreator = "����������";
$langSurveyCourse = "������";
$langSurveyCreationError = "������ ���� ��� ���������� ��� ������������. �������� ����������� ����.";
$langSurveyDeactivate = "��������������";
$langSurveyActivate = "������������";
$langSurveyParticipate = "���������";
$langSurveyDeleted ="� ������ ���������� ������ ���������� ��������. ������� <a href=\"survey.php\">���</a> ��� �� ������������ ��� ������ ��� ������� ���������� ������.";
$langSurveyDeactivated ="� ������ ���������� ������ ���������������� ��������. ������� <a href=\"survey.php\">���</a> ��� �� ������������ ��� ������ ��� ������� ���������� ������.";
$langSurveyActivated ="� ������ ���������� ������ �������������� ��������. ������� <a href=\"survey.php\">���</a> ��� �� ������������ ��� ������ ��� ������� ���������� ������.";
$langSurveySubmitted ="� ������ ���������� ������ ���������� ��������. ������� <a href=\"survey.php\">���</a> ��� �� ������������ ��� ������ ��� ������� ���������� ������.";
$langSurveyUser = "�������";
$langSurveyTotalAnswers = "��������� ������� ����������";
$langSurveyNone = "��� ����� ������������ ������� ���������� ������ ��� �� ������";
$langSurveyInactive = "� ������ ���������� ������ ��� ����� � ��� ���� ������������� �����.";
$langSurveyCharts = "������������� ������������";
$langSurveyIndividuals = "������������ ��� ������";

$langTestEcho = "������� �� echo";
$langPollsActive = "������� �������������";
$langPollsInactive = "������� �������������";
$langPollName = "�����";
$langPollNumAnswers = "����������";
$langPollCreation = "����������";
$langPollStart = "������";
$langPollEnd = "����";
$langPollOperations = "�����������";
$langPollEdit = "�����������";
$langPollRemove = "��������";
$langPollCreate = "����������";
$langPollQuestion = "�������";
$langPollAnswer = "��������";
$langPollAddAnswer = "�������� ����������";
$langPollType = "�����";
$langPollMC = "��������� ��������";
$langPollFillText = "����������� �� ����";
$langPollContinue = "��������";
$langPollMoreAnswers = "�� ����� ����������";
$langPollYes = "���";
$langPollNo = "���";
$langPollMoreAnswers ="�� ����� ����������";
$langPollMoreQuestions = "�� ����� ���������";
$langPollCreate = "���������� ������������";
$langPollCreated ="� ����������� ������������� �� ��������. ������� <a href=\"poll.php\">���</a> ��� �� ������������ ��� ������ ��� �������������.";
$langPollCreator = "����������";
$langPollCourse = "������";
$langPollCreationError = "������ ���� ��� ���������� ��� ������������. �������� ����������� ����.";
$langPollDeactivate = "��������������";
$langPollActivate = "������������";
$langPollParticipate = "���������";
$langPollDeleted ="� ����������� ���������� ��������. ������� <a href=\"poll.php\">���</a> ��� �� ������������ ��� ������ ��� �������������.";
$langPollDeactivated ="� ����������� ���������������� ��������. ������� <a href=\"poll.php\">���</a> ��� �� ������������ ��� ������ ��� �������������.";
$langPollActivated ="� ����������� �������������� ��������. ������� <a href=\"poll.php\">���</a> ��� �� ������������ ��� ������ ��� �������������.";
$langPollSubmitted ="� ����������� ���������� ��������. ������� <a href=\"poll.php\">���</a> ��� �� ������������ ��� ������ ��� �������������.";
$langPollUser = "�������";
$langPollTotalAnswers = "��������� ������� ����������";
$langPollNone = "��� �������� ���� ��� ������ ���������� �������������.";
$langPollInactive = "� ����������� ��� ����� � ��� ���� ������������� �����.";
$langPollCharts = "������������� ������������";
$langPollIndividuals = "������������ ��� ������";

$langQPref = "�� ���� ��������������� ����������;";
$langQPrefSurvey = "������ ���������� ������";
$langQPrefPoll = "�����������";

$langNamesPoll = "�������������";
$langNamesSurvey = "������� ���������� ������";
?>
