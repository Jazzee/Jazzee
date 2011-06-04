/**
 * The JazzeeEntityElementRankingList type
  @extends ApplyElement
 */
function JazzeeEntityElementRankingList(){}
JazzeeEntityElementRankingList.prototype = new List();
JazzeeEntityElementRankingList.prototype.constructor = JazzeeEntityElementRankingList;

/**
 * Override JazzeeElement new page to set min/max defaults
 * @param {String} id the id to use
 * @returns {JazzeeEntityPageBranching}
 */
JazzeeEntityElementRankingList.prototype.newElement = function(id,title,classId,className,status,page){
  var element = JazzeeElement.prototype.newElement.call(this,id,title,classId,className,status,page);
  element.min = 1;
  element.max = 1;
  return element;
};

JazzeeEntityElementRankingList.prototype.avatar = function(){
  var div = $('<div>');
  for(var i=0; i<this.max; i++){
	  var select = $('<select>');
	  for(var j in this.listItems){
	    select.append($('<option>').html(this.listItems[j].value));
	  }
	  div.append(select);
  }
  return div;
};

/**
 * Add required and maximum items to optsions
 * @returns {jQuery}
 */
JazzeeEntityElementRankingList.prototype.optionsBlock = function(){
  var element = this;
  var optionsBlockDiv = List.prototype.optionsBlock.call(this);
  optionsBlockDiv.append(this.requiredItemsBlock());
  optionsBlockDiv.append(this.maximumItemsBlock());
  return optionsBlockDiv;
};

/**
 * Required items to rank
 * @return {jQuery}
 */
JazzeeEntityElementRankingList.prototype.requiredItemsBlock = function(){
  var elementClass = this;
  var p = $('<p>').addClass('edit').html('Require ').append($('<span>').html(this.min + ' items to be ranked').bind('click',function(e){
    $(this).unbind('click');
    var select = $('<select>');
    for(var i=1; i< 50; i++){
      var option = $('<option>').attr('value', i).html(i);
      if(elementClass.min == i) option.attr('selected', true);
      select.append(option);
    }
    select.bind('change', function(e){
      elementClass.min = $(this).val();
      elementClass.isModified = true;
    });
    select.bind('blur', function(e){
     p.replaceWith(elementClass.requiredItemsBlock());
    });
    $(this).empty().append(select);
  }));
  return p;
};

/**
 * Maximum allowed items
 * @return {jQuery}
 */
JazzeeEntityElementRankingList.prototype.maximumItemsBlock = function(){
  var elementClass = this;
  var p = $('<p>').addClass('edit').html('Allow up to ').append($('<span>').html(this.max + ' items to be ranked').bind('click',function(e){
    $(this).unbind('click');
    var select = $('<select>');
    for(var i=1; i< 50; i++){
      var option = $('<option>').attr('value', i).html(i);
      if(elementClass.max == i) option.attr('selected', true);
      select.append(option);
    }
    select.bind('change', function(e){
      elementClass.max = $(this).val();
      elementClass.isModified = true;
    });
    select.bind('blur', function(e){
     p.replaceWith(elementClass.maximumItemsBlock());
    });
    $(this).empty().append(select);
  }));
  return p;
};