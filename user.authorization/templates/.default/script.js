$(document).ready(function () {

    var userAuth;
    var timerAuth;

    function setParamsAuthComponent() {

        userAuth = new UserAuth();
        userAuth.componentName = 'userAuth';
        userAuth.componentContainerDiv = $('.authBlock');
        userAuth.componentForm = $('#authPhone');
        userAuth.componentMessageContainer = $('.authBlock').find('.infoText');
        userAuth.loaderImageBlock = $('.authBlock').find('.formPreloader');
        userAuth.inputBlockAuth = $('.authBlock').find('.inputBlockAuth');
        userAuth.inputEnteredCode = $('.authBlock').find('.inputBlockCode');
        userAuth.redirectUrl = ('undefined' !== returnPageUrl) ? returnPageUrl : window.location.href;
    }

    //send request get confirm code
    $(document).on('click', '.authBlock .authGetCode', function () {
        setParamsAuthComponent();
        userAuth.getConfirmCode($('.authBlock').find('.inputBlockAuth'));
        initTimerLockAuth();
    });
    //repeat request get confirm code
    $(document).on('click', '.authBlock .repeatCode', function () {
        userAuth.getConfirmCode($('.authBlock').find('.inputBlockCode'));
        initTimerLockAuth();
    });

    //send request with entered confirm code
    $(document).on('keyup', '.authBlock .phoneCode', function () {

        var code = $(this).val();
        var codeLength = $(this).val().length;
        userAuth.enterConfirmCode(code, codeLength);
    });

    function initTimerLockAuth() {
        timerAuth = new TimerLookSend();
        timerAuth.parentBlock = $('.inputBlockCode');
        timerAuth.createProperty(60);
        timerAuth.createTimerHtml();
        timerAuth.runTimer();
    }
});


function UserAuth() {
    UserEntry.call(this);

    this.loaderImageBlock = '';
    this.inputBlockAuth = '';
    this.inputEnteredCode = '';
};

UserAuth.prototype = Object.create(UserEntry.prototype);
UserAuth.prototype.getConfirmCode = function (blockForm) {
    if (validateAuthForm(this.componentForm)) {
        this.showHideBlock(this.loaderImageBlock, blockForm);

        var dataForm = this.prepareDataFormGetCode();
        return this.sendRequestAjax(dataForm, this, 'handlerResponseGetCode');
    }
};
UserAuth.prototype.prepareDataFormGetCode = function() {
    var result = this.componentForm.serialize();
    result += '&component=' + this.componentName + '&action=authUserGetCodeAction';
    return result;
};

UserAuth.prototype.getConfirmCodeRepeat = function() {
    if (validateAuthForm(this.componentForm)) {
        this.showHideBlock(this.loaderImageBlock, this.inputEnteredCode);


    }
};
UserAuth.prototype.handlerResponseGetCode = function (dataResponse) {
    this.handlerMessageResponse(dataResponse);
    if (dataResponse.success === true) {
        this.dataServer = dataResponse.data;
        return this.showHideBlock(this.inputEnteredCode, this.loaderImageBlock)
    }
    this.showHideBlock(this.inputBlockAuth, this.loaderImageBlock);

    return true;
};
UserAuth.prototype.enterConfirmCode = function (codeConfirm, codeLength) {
    if (this.validateConfirmCodeLength(codeLength)) {

        this.showHideBlock(this.loaderImageBlock, this.inputEnteredCode);

        var dataForm = 'enterCode=' + codeConfirm;
        dataForm += '&originalCode=' + this.dataServer.crypt;
        dataForm += '&component=' + this.componentName;
        dataForm += '&action=enterCodeAuthAction';
        dataForm += '&phoneUser=' + this.dataServer.phoneUser;

        return this.sendRequestAjax(dataForm, this, 'handlerEnterConfirmCodeResponse');
    }
};
UserAuth.prototype.handlerEnterConfirmCodeResponse = function (dataResponse) {
    this.handlerMessageResponse(dataResponse);
    this.showHideBlock(this.inputEnteredCode, this.loaderImageBlock);

    if (dataResponse.success === true) {

        this.dataServer = dataResponse.data;
        if (this.dataServer.auth === true) {
            this.redirectTimeOut();
        }
    }

    return true;
};
UserAuth.prototype.redirectTimeOut = function () {
    setTimeout(this.redirectUser(), 500);
};
UserAuth.prototype.redirectUser = function () {
    window.location.href = this.redirectUrl;
};

/**
 * The login form validation method
 *
 * @param {object} loginFormSelector Login form selector
 *
 * @return {boolean} true|false Result of form validation
 */
function validateAuthForm(loginFormSelector) {
    loginFormSelector.validate({
        rules: {
            phoneUser: {
                required: true,
                minlength: 10,
                maxlength: 10
            }
        },
        messages: {
            phoneUser: {
                required: 'Необходимо указать телефон',
                minlength: 'Минимум 10 символов',
                maxlength: 'Максимум 10 символов'
            }
        }
    });

    return loginFormSelector.valid();
}
