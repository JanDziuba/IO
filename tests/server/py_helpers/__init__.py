import psycopg2
import requests

CONNECTION_STR = "dbname='learnio' user='learnio' host='localhost' password='1234'"
SERVER_URL = "http://localhost:3000/"
REGISTER_URL = "server/register.php"
LOGIN_URL = "server/login.php"
CREATE_LESSON_URL = "server/createNewLesson.php"
REMOVE_LESSON_URL = "server/removeLesson.php"
ADD_LESSON_ENTRY_URL = "server/addLessonEntry.php"
MODIFY_LESSON_ENTRY_URL = "server/editLessonEntry.php"
REMOVE_LESSON_ENTRY_URL = "server/removeLessonEntry.php"

def executeQueries(queries):
    try:
        with psycopg2.connect(CONNECTION_STR) as conn:
            with conn.cursor() as cursor:
                for query in queries:
                    cursor.execute(query)
                conn.commit()
                return True
    except Exception as e:
        print("Query execution failed")
        print(e)
        return False

def getQueryResult(query):
    try:
        with psycopg2.connect(CONNECTION_STR) as conn:
            with conn.cursor() as cursor:
                cursor.execute(query)
                conn.commit()
                return cursor.fetchall()
    except Exception as e:
        print("Queries execution failed")
        print(e)
        return False

def resetDb():
    executeQueries(["DELETE FROM account;"])
    assert len(getQueryResult("SELECT * FROM account;")) == 0

def registerUser(login, password):
    r = requests.post(SERVER_URL + REGISTER_URL, data={'login':login, 'password':password})
    assert r.json()['result'] == 0

def createUserSession(login, password):
    session = requests.Session()
    r = session.post(SERVER_URL + LOGIN_URL, data={'login':login, 'password':password})
    assert r.json()['result'] == 0
    return session

def createLesson(session, name, description):
    r = session.get(SERVER_URL + CREATE_LESSON_URL, params={'name':name, 'description':description})
    parsedResult = r.json()
    assert parsedResult['result'] == 0
    return parsedResult['createdLessonId']

def removeLesson(session, lessonId):
    r = session.post(SERVER_URL + REMOVE_LESSON_URL, data={'lessonId':lessonId})
    parsedResult = r.json()
    return parsedResult['result'] == 0

def addLessonEntry(session, lessonId, question, answer):
    r = session.get(SERVER_URL + ADD_LESSON_ENTRY_URL, params={'lessonId':lessonId, 'question':question, 'answer':answer}).json()
    assert r['result'] == 0
    return r['createdEntryId']

def modifyLessonEntry(session, entryId, newQuestion, newAnswer):
    r = session.get(SERVER_URL + MODIFY_LESSON_ENTRY_URL, params={'entryId':entryId, 'newQuestion':newQuestion, 'newAnswer':newAnswer}).json()
    return r['result'] == 0

def removeLessonEntry(session, entryId):
    r = session.get(SERVER_URL + REMOVE_LESSON_ENTRY_URL, params={'entryId':entryId}).json()
    return r['result'] == 0

def createTestUserSetup(login = 'test'):
    registerUser(login, 'testtest')
    session = createUserSession(login, 'testtest')
    testLessonId = createLesson(session, 'lesson1', 'sample description')
    return (session, testLessonId)

def populateTestLesson(session, lessonId, start, end):
    for i in range(start, end):
        addLessonEntry(session, lessonId, "q" + str(i), "a" + str(i))
    return True
