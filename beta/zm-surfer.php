<?php

function show_module() {
	global $db, $startdate, $sqldater, $currentyear, $currentmonth, $monthy, $contract, $team_id_def, $teamdefinition, $uid, $app_action;

	// Big div.
	echo "<div class='big-div'>";

	if (isset($_GET['b'])) {
		$sql = "INSERT INTO diggeractivity (uid, survey_id) VALUES('$uid', '" . $_GET['b'] . "')";
		sq($sql, true);
	}

	// Find an unsurfed survey
	$survey = sqr
		(
			"SELECT
				raw_data.external_survey_id
			FROM raw_data
				$sqldater $teamdefinition
			AND raw_data.external_survey_id <> ''
			AND not exists (select 1 from diggeractivity WHERE diggeractivity.survey_id = raw_data.external_survey_id)
			"
		)
		['external_survey_id'];

	// Get all survey data
		$sql = "SELECT
		external_survey_id,
		teammate_nt_id,
		teammate_name,
		likely_to_recommend_paypal,
		issue_resolved,
		Handled_professionally,
		Showed_genuine_interest,
		Took_ownership,
		Knowledge_to_handle_request,
		Valued_customer,
		Was_professional,
		Easy_to_understand,
		Provided_accurate_info,
		Helpful_response,
		Answered_concisely,
		what_would_it_take_to_earn_hig,
		what_would_it_take_to_earn_10_,
		like_most_about_paypal__ltr_,
		what_could_be_done_differently,
		what_could_be_done_to_earn_10_,
		what_teammate_did_to_earn_sati,
		improve_knowledge_to_handle_re,
		improve_handled_professionally,
		improve_took_ownership,
		improve_genuine_interest,
		improve_valued_customer,
		why_issue_not_resolved,
		not_sure_issue_is_resolved,
		how_could_have_reduced_custome,
		customer_contact_count,
		customers_primary_country_of_,
		workitem_phone_talk_time,
		customer_account_id,
		teammate_tenure,
		kdi___email,
		kdi___phone,
		easy_to_handle_issue_inquiry,
		what_could_paypal_do_to_make_e,
		count(*) as incomplete
		FROM raw_data $sqldater $teamdefinition
		AND external_survey_id = '$survey'
		LIMIT 1";

		if(!$result = $db->query($sql)) {
			cl($sql);
			cl($db->error);
		}
		$row = $result->fetch_assoc();

		// Go through all unsurfed surveys.
		echo "<div class='big-quote'>";
			echo getUserBadge($row['teammate_nt_id'], 'hide_name');
			echo " &nbsp; ";
			echo $row['teammate_name'];
		echo "</div>";

		if ($row['customer_account_id'] != "") {
			echo "<div class='row'>";
			echo "<span class='surfer-label'>Account Number</span>";
			echo "<span class='surfer-data'>{$row['customer_account_id']}</span>";
			echo "</div>";
		}
		echo "<hr>";

		if ($row['likely_to_recommend_paypal'] != "") {
			echo "<div class='row'>";
			echo "<span class='surfer-label'>Likely To Recommend PayPal</span>";
			echo "<span class='surfer-data'>{$row['likely_to_recommend_paypal']}</span>";
			echo "</div>";
		}
		if ($row['what_would_it_take_to_earn_hig'] != "") {
			echo "<div class='row row-comment'>";
			echo "<span class='surfer-label'>What would it take to hearn a higher score?</span>";
			echo "<span class='surfer-comment'>{$row['what_would_it_take_to_earn_hig']}</span>";
			echo "</div>";
		}
		if ($row['what_would_it_take_to_earn_10_'] != "") {
			echo "<div class='row row-comment'>";
			echo "<span class='surfer-label'>What would it take to hearn 10?</span>";
			echo "<span class='surfer-comment'>{$row['what_would_it_take_to_earn_10_']}</span>";
			echo "</div>";
		}
		if ($row['like_most_about_paypal__ltr_'] != "") {
			echo "<div class='row row-comment'>";
			echo "<span class='surfer-label'>Like most about PayPal?</span>";
			echo "<span class='surfer-comment'>{$row['like_most_about_paypal__ltr_']}</span>";
			echo "</div>";
		}
		echo "<hr>";

		if ($row['what_could_be_done_differently'] != "") {
			echo "<div class='row row-comment'>";
			echo "<span class='surfer-label'>What could be done differently?</span>";
			echo "<span class='surfer-comment'>{$row['what_could_be_done_differently']}</span>";
			echo "</div>";
		}
		if ($row['what_could_be_done_to_earn_10_'] != "") {
			echo "<div class='row row-comment'>";
			echo "<span class='surfer-label'>What could be done to earn 10?</span>";
			echo "<span class='surfer-comment'>{$row['what_could_be_done_to_earn_10_']}</span>";
			echo "</div>";
		}
		if ($row['what_teammate_did_to_earn_sati'] != "") {
			echo "<div class='row row-comment'>";
			echo "<span class='surfer-label'>What teammate did to earn satisfaction</span>";
			echo "<span class='surfer-comment'>{$row['what_teammate_did_to_earn_sati']}</span>";
			echo "</div>";
		}
		echo "<hr>";

		if ($row['Handled_professionally'] != "") {
			echo "<div class='row'>";
			echo "<span class='surfer-label'>Handled Professionally</span>";
			echo "<span class='surfer-data'>{$row['Handled_professionally']}</span>";
			echo "</div>";
		}
		if ($row['Showed_genuine_interest'] != "") {
			echo "<div class='row'>";
			echo "<span class='surfer-label'>Showed Genuine Interest</span>";
			echo "<span class='surfer-data'>{$row['Showed_genuine_interest']}</span>";
			echo "</div>";
		}
		if ($row['Took_ownership'] != "") {
			echo "<div class='row'>";
			echo "<span class='surfer-label'>Took Ownership</span>";
			echo "<span class='surfer-data'>{$row['Took_ownership']}</span>";
			echo "</div>";
		}
		if ($row['Knowledge_to_handle_request'] != "") {
			echo "<div class='row'>";
			echo "<span class='surfer-label'>Knowledge to Handle Request</span>";
			echo "<span class='surfer-data'>{$row['Knowledge_to_handle_request']}</span>";
			echo "</div>";
		}
		if ($row['Valued_customer'] != "") {
			echo "<div class='row'>";
			echo "<span class='surfer-label'>Valued Customer</span>";
			echo "<span class='surfer-data'>{$row['valued_customer']}</span>";
			echo "</div>";
		}
		if ($row['Was_professional'] != "") {
			echo "<div class='row'>";
			echo "<span class='surfer-label'>Was Professional</span>";
			echo "<span class='surfer-data'>{$row['Was_professional']}</span>";
			echo "</div>";
		}
		if ($row['Easy_to_understand'] != "") {
			echo "<div class='row'>";
			echo "<span class='surfer-label'>Easy to Understand</span>";
			echo "<span class='surfer-data'>{$row['Easy_to_understand']}</span>";
			echo "</div>";
		}
		if ($row['Provided_accurate_info'] != "") {
			echo "<div class='row'>";
			echo "<span class='surfer-label'>Provided Accurate Information</span>";
			echo "<span class='surfer-data'>{$row['Provided_accurate_info']}</span>";
			echo "</div>";
		}
		if ($row['Helpful_response'] != "") {
			echo "<div class='row'>";
			echo "<span class='surfer-label'>Helpful Response</span>";
			echo "<span class='surfer-data'>{$row['Helpful_response']}</span>";
			echo "</div>";
		}
		if ($row['Answered_concisely'] != "") {
			echo "<div class='row'>";
			echo "<span class='surfer-label'>Answered Concisely</span>";
			echo "<span class='surfer-data'>{$row['Answered_concisely']}</span>";
			echo "</div>";
		}


		if ($row['improve_knowledge_to_handle_re'] != "") {
			echo "<div class='row row-comment'>";
			echo "<span class='surfer-label'>Improve Knowledge to Handle Request</span>";
			echo "<span class='surfer-comment'>{$row['improve_knowledge_to_handle_re']}</span>";
			echo "</div>";
		}
		if ($row['improve_handled_professionally'] != "") {
			echo "<div class='row row-comment'>";
			echo "<span class='surfer-label'>Improve Handled Professionally</span>";
			echo "<span class='surfer-comment'>{$row['improve_handled_professionally']}</span>";
			echo "</div>";
		}

		if ($row['improve_took_ownership'] != "") {
			echo "<div class='row row-comment'>";
			echo "<span class='surfer-label'>Improve Took Ownership</span>";
			echo "<span class='surfer-comment'>{$row['improve_took_ownership']}</span>";
			echo "</div>";
		}
		if ($row['improve_genuine_interest'] != "") {
			echo "<div class='row row-comment'>";
			echo "<span class='surfer-label'>Improve Genuine Interest</span>";
			echo "<span class='surfer-comment'>{$row['improve_genuine_interest']}</span>";
			echo "</div>";
		}
		if ($row['improve_valued_customer'] != "") {
			echo "<div class='row row-comment'>";
			echo "<span class='surfer-label'>Improve Valued Customer</span>";
			echo "<span class='surfer-comment'>{$row['improve_valued_customer']}</span>";
			echo "</div>";
		}

		echo "<hr>";

		if ($row['issue_resolved'] != "") {
			echo "<div class='row'>";
			echo "<span class='surfer-label'>Was the issue resolved?</span>";
			echo "<span class='surfer-data'>{$row['issue_resolved']}</span>";
			echo "</div>";
		}
		if ($row['why_issue_not_resolved'] != "") {
			echo "<div class='row row-comment'>";
			echo "<span class='surfer-label'>Why not?</span>";
			echo "<span class='surfer-comment'>{$row['why_issue_not_resolved']}</span>";
			echo "</div>";
		}
		if ($row['not_sure_issue_is_resolved'] != "") {
			echo "<div class='row row-comment'>";
			echo "<span class='surfer-label'>Why not sure?</span>";
			echo "<span class='surfer-comment'>{$row['not_sure_issue_is_resolved']}</span>";
			echo "</div>";
		}
		echo "<hr>";
		if ($row['easy_to_handle_issue_inquiry'] != "") {
			echo "<div class='row'>";
			echo "<span class='surfer-label'>Easy to handle issue</span>";
			echo "<span class='surfer-data'>{$row['easy_to_handle_issue_inquiry']}</span>";
			echo "</div>";
		}
		if ($row['how_could_have_reduced_custome'] != "") {
			echo "<div class='row row-comment'>";
			echo "<span class='surfer-label'>How could have reduced customer effort</span>";
			echo "<span class='surfer-comment'>{$row['how_could_have_reduced_custome']}</span>";
			echo "</div>";
		}
		if ($row['what_could_paypal_do_to_make_e'] != "") {
			echo "<div class='row row-comment'>";
			echo "<span class='surfer-label'>What could PayPal do to make it easier?</span>";
			echo "<span class='surfer-comment'>{$row['what_could_paypal_do_to_make_e']}</span>";
			echo "</div>";
		}

		echo "<a class='' href='./?a=surfer&b={$survey}'>
		<div class='surfer-check'>
			<span>&#10004;</span>
		</div>
		</a>";


	// End big div
	echo "</div>";
}

function dug($column_name, $survey_id) {
	if (isset(db_get("SELECT column_name FROM diggeractivity WHERE column_name = '{$column_name}' AND survey_id = '{$survey_id}' AND uid = '{$_SESSION['user_id']}' LIMIT 1")['column_name'])) {
		return true;
	}

	return false;
}

?>
