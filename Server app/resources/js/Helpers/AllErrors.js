const toastr = require('toastr');
export default class AllErrors {
  static getError(errors){
    var allErrors = errors;
    if (typeof(allErrors) == 'object') {
      for (var errorkey in allErrors) {
        if (allErrors[errorkey]){
          toastr.error(allErrors[errorkey], 'Error');
        }
      }
    }else{
      toastr.error(allErrors, 'Error');
    }
  }

  static verifyField(errors){
    for (var i = 0; i < errors.length; i++) {
      if(errors[i].input == null || errors[i].input == ''){
         toastr.error('Por favor rellene el campo ' + errors[i].label, 'Error');
         return true;
      }else if(typeof errors[i].input == 'object'){
        if(errors[i].input.value == null || errors[i].input.value == ''){
          toastr.error('Por favor rellene el campo ' + errors[i].label, 'Error');
          return true;
        }
      }
    }
    return false;
  }
}
