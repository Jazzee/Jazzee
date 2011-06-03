/**
 * The JazzeeEntityElementRankingList type
  @extends ApplyElement
 */
function JazzeeEntityElementRankingList(){}
JazzeeEntityElementRankingList.prototype = new List();
JazzeeEntityElementRankingList.prototype.constructor = JazzeeEntityElementRankingList;

JazzeeEntityElementRankingList.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};