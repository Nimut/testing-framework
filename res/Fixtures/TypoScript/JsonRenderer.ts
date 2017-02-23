config {
    no_cache = 1
    debug = 0
    xhtml_cleaning = 0
    admPanel = 0
    disableAllHeaderCode = 1
    sendCacheHeaders = 0
    sys_language_uid = 0
    sys_language_mode = ignore
    sys_language_overlay = 1
    additionalHeaders = Content-Type: application/json; charset=utf-8

    watcher {
        tableFields {
            pages = uid,_ORIG_uid,pid,sorting,title
            sys_category = uid,_ORIG_uid,_LOCALIZED_UID,pid,sys_language_uid,title,parent,items,sys_language_uid
            sys_file = uid,_ORIG_uid,_LOCALIZED_UID,pid,title,sys_language_uid
            sys_file_reference = uid,_ORIG_uid,_LOCALIZED_UID,title,description,alternative,link,downloadname,missing,identifier,file,pid,sys_language_uid,title,parent,items,sys_language_uid,uid_local,uid_foreign,tablenames,fieldname,table_local
            tt_content = uid,_ORIG_uid,_LOCALIZED_UID,pid,sorting,sys_language_uid,header,categories
        }
    }
}

lib.watcherDataObject = COA
lib.watcherDataObject {
    1 = LOAD_REGISTER
    1.watcher.dataWrap = |
    2 = USER
    2.userFunc = Nimut\TestingFramework\Frontend\Collector->addRecordData
    99 = RESTORE_REGISTER
}

lib.watcherFileObject = COA
lib.watcherFileObject {
    1 = LOAD_REGISTER
    1.watcher.dataWrap = |
    2 = USER
    2.userFunc = Nimut\TestingFramework\Frontend\Collector->addFileData
    99 = RESTORE_REGISTER
}

page = PAGE
page {
    10 = COA
    10 {
        1 = LOAD_REGISTER
        1.watcher.dataWrap = pages:{field:uid}
        2 = USER
        2.userFunc = Nimut\TestingFramework\Frontend\Collector->addRecordData
        10 = CONTENT
        10 {
            stdWrap.required = 1
            table = pages
            select {
                orderBy = sorting
                pidInList = this
                # prevent sys_language_uid lookup
                languageField = 0
            }

            renderObj < lib.watcherDataObject
            renderObj.1.watcher.dataWrap = {register:watcher}|.__pages/pages:{field:uid}
        }

        20 = CONTENT
        20 {
            table = tt_content
            select {
                orderBy = sorting
                where = colPos=0
            }

            renderObj < lib.watcherDataObject
            renderObj.1.watcher.dataWrap = {register:watcher}|.__contents/tt_content:{field:uid}
            renderObj {
                10 = CONTENT
                10 {
                    if.isTrue.field = categories
                    table = sys_category
                    select {
                        pidInList = root,-1
                        selectFields = sys_category.*
                        join = sys_category_record_mm ON sys_category_record_mm.uid_local = sys_category.uid
                        where.data = field:_ORIG_uid // field:uid
                        where.intval = 1
                        where.wrap = sys_category_record_mm.uid_foreign=|
                        orderBy = sys_category_record_mm.sorting_foreign
                        languageField = sys_category.sys_language_uid
                    }

                    renderObj < lib.watcherDataObject
                    renderObj.1.watcher.dataWrap = {register:watcher}|.categories/sys_category:{field:uid}
                }

                40 = FILES
                40 {
                    if.isTrue.field = image
                    references {
                        fieldName = image
                    }

                    renderObj < lib.watcherFileObject
                    renderObj.1.watcher.dataWrap = {register:watcher}|.image/
                }
            }
        }

        stdWrap.postUserFunc = Nimut\TestingFramework\Frontend\Collector->attachSection
        stdWrap.postUserFunc.as = Default
    }

    stdWrap.postUserFunc = Nimut\TestingFramework\Frontend\Renderer->renderSections
}

[globalVar = GP:L = 1]
    config.sys_language_uid = 1
[end]

[globalVar = GP:L = 2]
    config.sys_language_uid = 2
[end]
