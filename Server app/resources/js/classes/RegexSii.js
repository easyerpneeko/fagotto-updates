export default class RegexSii {
  static testRut(value){
    var reg = new RegExp(/^[0-9]{0,9}(-[0-9]{0,9})?$/);
    return reg.test(value);
  }

  static testPhone(value){
    var reg = new RegExp(/^\d+$/);
    if(!reg.test(value)) return false;
    if(value.toString().length < 10) return false;
    return true;
  }

  static testNumeric(value){
    var reg = new RegExp(/^\d+$/);
    return reg.test(value)
  }
}
