<?php
/*
   This file is part of CoDev-Timetracking.

   CoDev-Timetracking is free software: you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   CoDev-Timetracking is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with CoDev-Timetracking.  If not, see <http://www.gnu.org/licenses/>.
*/

class IssueInfoTools {

   /**
    * Initialize complex static variables
    * @static
    */
   public static function staticInit() {
      // Nothing special
   }

   /**
    * Get general info of an issue
    * @param Issue $issue The issue
    * @param bool $isManager if true: show MgrEffortEstim column
    * @param bool $displaySupport If true, display support
    * @return mixed[]
    */
   public static function getIssueGeneralInfo(Issue $issue, $isManager=false, $displaySupport=false) {
      $withSupport = true;  // include support in elapsed & Drift

      $drift = $issue->getDrift($withSupport);
      $issueGeneralInfo = array(
         "issueId" => $issue->getId(),
         "issueSummary" => htmlspecialchars(preg_replace('![\t\r\n]+!',' ',$issue->getSummary())),
         "issueType" => $issue->getType(),
         "issueDescription" => htmlspecialchars($issue->getDescription()),
         "projectName" => $issue->getProjectName(),
         "categoryName" => $issue->getCategoryName(),
         "issueExtRef" => $issue->getTcId(),
         'mantisURL'=> Tools::mantisIssueURL($issue->getId(), NULL, true),
         'issueURL' => Tools::mantisIssueURL($issue->getId()),
         'statusName'=> $issue->getCurrentStatusName(),
         'priorityName'=> $issue->getPriorityName(),
         'severityName'=> $issue->getSeverityName(),
         'handlerName'=> UserCache::getInstance()->getUser($issue->getHandlerId())->getName(),

         "issueEffortTitle" => $issue->getEffortEstim().' + '.$issue->getEffortAdd(),
         "issueEffort" => $issue->getEffortEstim() + $issue->getEffortAdd(),
         "issueReestimated" => $issue->getReestimated(),
         "issueBacklog" => $issue->getBacklog(),
         "issueDriftColor" => $issue->getDriftColor($drift),
         "issueDrift" => round($drift, 2),
         "progress" => round(100 * $issue->getProgress()),
         'relationships' => self::getFormattedRelationshipsInfo($issue),
      	);
      if($isManager) {
         $issueGeneralInfo['issueMgrEffortEstim'] = $issue->getMgrEffortEstim();
         $driftMgr = $issue->getDriftMgr($withSupport);
         $issueGeneralInfo['issueDriftMgrColor'] = $issue->getDriftColor($driftMgr);
         $issueGeneralInfo['issueDriftMgr'] = round($driftMgr, 2);
      }
      if ($withSupport) {
         $issueGeneralInfo['issueElapsed'] = $issue->getElapsed();
      } else {
         $issueGeneralInfo['issueElapsed'] = $issue->getElapsed() - $issue->getElapsed(Jobs::JOB_SUPPORT);
      }
      if ($displaySupport) {
         if ($isManager) {
            $driftMgr = $issue->getDriftMgr(!$withSupport);
            $issueGeneralInfo['issueDriftMgrSupportColor'] = $issue->getDriftColor($driftMgr);
            $issueGeneralInfo['issueDriftMgrSupport'] = round($driftMgr, 2);
         }
         $drift = $issue->getDrift(!$withSupport);
         $issueGeneralInfo['issueDriftSupportColor'] = $issue->getDriftColor($drift);
         $issueGeneralInfo['issueDriftSupport'] = round($drift, 2);
      }

      return $issueGeneralInfo;
   }

   /**
    *
    * @param type $relationshipList
    */
   private static function getFormattedRelationshipsInfo($issue) {
      
      $relationships = $issue->getRelationships();

      $relationshipsInfo = array();
      foreach ($relationships as $relType => $bugids) {
         $typeLabel = Issue::getRelationshipLabel($relType);

         foreach ($bugids as $bugid) {
            $relatedIssue = IssueCache::getInstance()->getIssue($bugid);
            $relationshipsInfo["$bugid"] = array('url' => Tools::issueInfoURL($bugid),
                                                 'relationship' => $typeLabel,
                                                 'status' => $relatedIssue->getCurrentStatusName(),
                                                 'progress' => round(100 * $relatedIssue->getProgress()),
                                                 );
         }
      }
      ksort($relationshipsInfo);
      return $relationshipsInfo;
   }
   
}

// Initialize complex static variables
IssueInfoTools::staticInit();

?>
