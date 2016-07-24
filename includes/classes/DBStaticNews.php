<?php

class DBStaticNews {

  public static function db_news_update_set($announce_time, $text, $detail_url, $announce_id) {
    doquery("UPDATE {{announce}} SET `tsTimeStamp` = FROM_UNIXTIME({$announce_time}), `strAnnounce`='{$text}', detail_url = '{$detail_url}' WHERE `idAnnounce`={$announce_id};");
  }

  public static function db_news_insert_set($announce_time, $text, $detail_url, $user) {
    doquery("INSERT INTO {{announce}}
        SET `tsTimeStamp` = FROM_UNIXTIME({$announce_time}), `strAnnounce`='{$text}', detail_url = '{$detail_url}',
        `user_id` = {$user['id']}, `user_name` = '" . db_escape($user['username']) . "'");
  }

  public static function db_news_delete_by_id($announce_id) {
    doquery("DELETE FROM {{announce}} WHERE `idAnnounce` = {$announce_id} LIMIT 1;");
  }

  public static function db_news_with_survey_select_by_id($announce_id) {
    return doquery(
      "SELECT a.*, s.survey_id, s.survey_question, s.survey_until
        FROM {{announce}} AS a
        LEFT JOIN {{survey}} AS s ON s.survey_announce_id = a.idAnnounce
        WHERE `idAnnounce` = {$announce_id} LIMIT 1;", true);
  }

  /**
   * @param $template
   * @param $query_where
   * @param $query_limit
   *
   * @return array|bool|mysqli_result|null
   */
  public static function db_news_list_get_by_query(&$template, $query_where, $query_limit) {
    $announce_list = doquery(
      "SELECT a.*, UNIX_TIMESTAMP(`tsTimeStamp`) AS unix_time, u.authlevel, s.*
    FROM
      {{announce}} AS a
      LEFT JOIN {{survey}} AS s ON s.survey_announce_id = a.idAnnounce
      LEFT JOIN {{users}} AS u ON u.id = a.user_id
    {$query_where}
    ORDER BY `tsTimeStamp` DESC, idAnnounce" .
      ($query_limit ? " LIMIT {$query_limit}" : ''));

    $template->assign_var('NEWS_COUNT', classSupernova::$db->db_num_rows($announce_list));

    return $announce_list;
  }

}