<?php
/**
 * @author   Natan Felles <natanfelles@gmail.com>
 */
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('add_foreign_key'))
{
	/**
	 * @param string $table       Table name
	 * @param string $foreign_key Collumn name having the Foreign Key
	 * @param string $references  Table and column reference. Ex: users(id)
	 * @param string $on_delete   RESTRICT, NO ACTION, CASCADE, SET NULL, SET DEFAULT
	 * @param string $on_update   RESTRICT, NO ACTION, CASCADE, SET NULL, SET DEFAULT
	 *
	 * @return string SQL command
	 */
	function add_foreign_key($table, $foreign_key, $references, $on_delete = 'RESTRICT', $on_update = 'RESTRICT')
	{
		$references = explode('(', str_replace(')', '', str_replace('`', '', $references)));

		return "ALTER TABLE `{$table}` ADD CONSTRAINT `{$table}_{$foreign_key}_fk` FOREIGN KEY (`{$foreign_key}`) REFERENCES `{$references[0]}`(`{$references[1]}`) ON DELETE {$on_delete} ON UPDATE {$on_update}";
	}
}

if ( ! function_exists('drop_foreign_key'))
{
	/**
	 * @param string $table       Table name
	 * @param string $foreign_key Collumn name having the Foreign Key
	 *
	 * @return string SQL command
	 */
	function drop_foreign_key($table, $foreign_key)
	{
		return "ALTER TABLE `{$table}` DROP FOREIGN KEY `{$table}_{$foreign_key}_fk`";
	}
}

if ( ! function_exists('add_trigger'))
{
	/**
	 * @param string $trigger_name Trigger name
	 * @param string $table        Table name
	 * @param string $statement    Command to run
	 * @param string $time         BEFORE or AFTER
	 * @param string $event        INSERT, UPDATE or DELETE
	 * @param string $type         FOR EACH ROW [FOLLOWS|PRECEDES]
	 *
	 * @return string SQL Command
	 */
	function add_trigger($trigger_name, $table, $statement, $time = 'BEFORE', $event = 'INSERT', $type = 'FOR EACH ROW')
	{
		return 'DELIMITER ;;' . PHP_EOL . "CREATE TRIGGER `{$trigger_name}` {$time} {$event} ON `{$table}` {$type}" . PHP_EOL . 'BEGIN' . PHP_EOL . $statement . PHP_EOL . 'END;' . PHP_EOL . 'DELIMITER ;;';
	}
}

if ( ! function_exists('drop_trigger'))
{
	/**
	 * @param string $trigger_name Trigger name
	 *
	 * @return string SQL Command
	 */
	function drop_trigger($trigger_name)
	{
		return "DROP TRIGGER {$trigger_name};";
	}
}
function getImageTemplate($vendor_id,$slug){
		$ci = & get_instance();
		$ci->load->database();
		$query = "select email_heading,email_subject,email_content,sms_content from  email_settings where vendor_id='".$vendor_id."' and slug='".$slug."'";
		$res = $ci->db->query($query)->row();
		return $res;
	}
	function test($content,$phone){

		$msg = $content;
		$phone = $phone;
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://api.twilio.com/2010-04-01/Accounts/AC50f42ad7e951316054995622f3937c96/Messages.json',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => "To=$phone&MessagingServiceSid=MG57ffc0554ac1203ecb82affcc611fda1&Body=$msg",
		  CURLOPT_HTTPHEADER => array(
		    'Authorization: Basic QUM1MGY0MmFkN2U5NTEzMTYwNTQ5OTU2MjJmMzkzN2M5Njo2ZWJlODk5NmZhY2Q3NzYyNTI0YzQ2ZmNjZDMwOWNiYQ==',
		    'Content-Type: application/x-www-form-urlencoded'
		  ),
		));
		$response = curl_exec($curl);
		curl_close($curl);
	}

