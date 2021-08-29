import requests
import unittest
from py_helpers import SERVER_URL, resetDb, createTestUserSetup, populateTestLesson, removeLesson, addLessonEntry, removeLessonEntry, modifyLessonEntry
import json

START_TRAINING_URL = "server/startTraining.php"
LIST_TRAININGS_URL = "server/listTrainings.php"
GET_NEXT_TRAINING_BATCH_URL = "server/getNextTrainingBatch.php"
END_TRAINING_URL = "server/endTraining.php"
SUBMIT_TRAINING_RESULT_URL = "server/submitTrainingResult.php"
DEF_BATCH_SIZE = 5
DEF_TRAINING_REPETITIONS = 2
HTTP_OK = 200

def createTraining(session, lessonId, batchSize, trainingRepetitions):
    return session.post(SERVER_URL + START_TRAINING_URL, data={'lessonId':lessonId, 'batchSize':batchSize, 'trainingRepetitions':trainingRepetitions})

def getTrainings(session):
    return session.get(SERVER_URL + LIST_TRAININGS_URL)

class TrainingCreation(unittest.TestCase):
    def setUp(self):
        resetDb()
        (self.session, self.lessonId) = createTestUserSetup()
    def test_training_creation_method(self):
        r = createTraining(self.session, self.lessonId, DEF_BATCH_SIZE, DEF_TRAINING_REPETITIONS)
        self.assertEqual(r.status_code, HTTP_OK, "Training creation method existence")
    def test_successful_training_creation(self):
        result = createTraining(self.session, self.lessonId, DEF_BATCH_SIZE, DEF_TRAINING_REPETITIONS).json()
        self.assertEqual(result['result'], 0, "Successful training creation should return result 0")
    def test_successful_training_creation_result_format(self):
        r = createTraining(self.session, self.lessonId, DEF_BATCH_SIZE, DEF_TRAINING_REPETITIONS).json()
        self.assertIsNotNone(r.get('startedTrainingId'))
    def test_training_creation_request_invalid_method(self):
        r = self.session.get(SERVER_URL + START_TRAINING_URL, params={'lessonId':self.lessonId, 'batchSize':DEF_BATCH_SIZE, 'trainingRepetitions':DEF_TRAINING_REPETITIONS}).json()
        self.assertNotEqual(r['result'], 0)
    def test_no_session_training_creation_request(self):
        r = createTraining(requests, self.lessonId, DEF_BATCH_SIZE, DEF_TRAINING_REPETITIONS).json()
        self.assertEqual(r['result'], 1, "Training creation request without valid session should return result 1")
    def test_missing_param_training_creation_request(self):
        lessonId = self.lessonId
        batchSize = DEF_BATCH_SIZE
        trainingRepetitions = DEF_TRAINING_REPETITIONS
        r1 = self.session.post(SERVER_URL + START_TRAINING_URL, data={'batchSize':batchSize, 'trainingRepetitions':trainingRepetitions}).json()
        r2 = self.session.post(SERVER_URL + START_TRAINING_URL, data={'lessonId':lessonId, 'trainingRepetitions':trainingRepetitions}).json()
        r3 = self.session.post(SERVER_URL + START_TRAINING_URL, data={'lessonId':lessonId, 'batchSize':batchSize}).json()
        self.assertEqual(r1['result'], 2, "Training creation request missing required param should return result 2")
        self.assertEqual(r2['result'], 2, "Training creation request missing required param should return result 2")
        self.assertEqual(r3['result'], 2, "Training creation request missing required param should return result 2") 
    def test_missing_lesson_training_creation_request(self):
        r = createTraining(self.session, self.lessonId + 1, DEF_BATCH_SIZE, DEF_TRAINING_REPETITIONS).json()
        self.assertEqual(r['result'], 3, "Training creation request missing lesson should return result 3")
    def test_missing_lesson_privileges_training_creation_request(self):
        (_, lesson2Id) = createTestUserSetup(login='test2')
        r = createTraining(self.session, lesson2Id, DEF_BATCH_SIZE, DEF_TRAINING_REPETITIONS).json()
        self.assertEqual(r['result'], 3, "Training creation request missing lesson access privileges should return result 3")
    def test_training_creation_request_invalid_batch_size(self):
        for batchSize in ("abba", "", -2):
            r = createTraining(self.session, self.lessonId, batchSize, DEF_TRAINING_REPETITIONS).json()
            self.assertEqual(r['result'], 4, "Training creation request invalid batch size should return result 4")
    def test_training_creation_request_invalid_repetitions(self):
        for trainingRepetition in ("abba", "", -2):
            r = createTraining(self.session, self.lessonId, DEF_BATCH_SIZE, trainingRepetition).json()
            self.assertEqual(r['result'], 5, "Training creation request invalid trainings repetitions should return result 5")
    def test_training_creation_invalid_name(self):
        for name in [("0123456789" * 10)]:
            r = self.session.post(SERVER_URL + START_TRAINING_URL, data={'lessonId':self.lessonId, 'batchSize':DEF_BATCH_SIZE, 'trainingRepetitions':DEF_TRAINING_REPETITIONS, 'name':name}).json()
            self.assertEqual(r['result'], 6, "Training creation request invalid name should return result 6")

class TrainingsListing(unittest.TestCase):
    def setUp(self):
        resetDb()
        (self.session, self.lessonId) = createTestUserSetup()
    def test_trainings_listing_method(self):
        r = getTrainings(self.session)
        self.assertEqual(r.status_code, HTTP_OK, "Training listing method existence")
    def test_trainings_listing_result_length(self):
        r1 = getTrainings(self.session).json()
        self.assertEqual(r1['result'], 0, "Successful listing method result")
        self.assertEqual(len(r1['data']), 0, "Listing empty list")
        # adding some trainings
        createTraining(self.session, self.lessonId, DEF_BATCH_SIZE, DEF_TRAINING_REPETITIONS)
        # now, listing should return one training
        r2 = getTrainings(self.session).json()
        self.assertEqual(r2['result'], 0, "Successful listing method result")
        self.assertEqual(len(r2['data']), 1, "Listing non-empty list")
        #adding some more trainings
        for _ in range(42):
            createTraining(self.session, self.lessonId, DEF_BATCH_SIZE, DEF_TRAINING_REPETITIONS)
        # now, listing should return 43 trainings
        r3 = getTrainings(self.session).json()
        self.assertEqual(r3['result'], 0, "Successful listing method result")
        self.assertEqual(len(r3['data']), 43, "Listing non-empty list")
    def test_training_listing_result_format(self):
        createTraining(self.session, self.lessonId, DEF_BATCH_SIZE, DEF_TRAINING_REPETITIONS)
        r1 = getTrainings(self.session).json()
        self.assertEqual(r1['result'], 0, "Successful listing method result")
        self.assertEqual(len(r1['data']), 1, "Listing non-empty list")
        trainingEntry = r1['data'][0]
        self.assertIsNotNone(trainingEntry['id'])
        self.assertEqual(int(trainingEntry['batchsize']), DEF_BATCH_SIZE, "Returned batch size valid value")
        self.assertEqual(int(trainingEntry['trainingrepetitions']), DEF_TRAINING_REPETITIONS, "Returned valid training Repetitions value")
        self.assertEqual(int(trainingEntry['lessonid']), self.lessonId, "Returned valid lesson id")
    def test_trainings_listing_invalid_method(self):
        r = self.session.post(SERVER_URL + LIST_TRAININGS_URL).json()
        self.assertEqual(r['result'], 4, "Handling only GET http method")
    def test_no_session_trainings_listing(self):
        r = getTrainings(requests).json()
        self.assertEqual(r['result'], 1)
    def test_trainings_listing_limit(self):
        #adding some trainings
        for _ in range(42):
            createTraining(self.session, self.lessonId, DEF_BATCH_SIZE, DEF_TRAINING_REPETITIONS)
        self.assertEqual(len(self.session.get(SERVER_URL + LIST_TRAININGS_URL, params={'limit':-1}).json()['data']), 42, "Listing non-empty list unlimited")
        self.assertEqual(len(self.session.get(SERVER_URL + LIST_TRAININGS_URL, params={'limit':1}).json()['data']), 1, "Listing non-empty list limited")
        self.assertEqual(len(self.session.get(SERVER_URL + LIST_TRAININGS_URL, params={'limit':20}).json()['data']), 20, "Listing non-empty list unlimited")
    def test_trainings_listing_invalid_limit(self):
        #adding some trainings
        for _ in range(42):
            createTraining(self.session, self.lessonId, DEF_BATCH_SIZE, DEF_TRAINING_REPETITIONS)
        self.assertEqual(self.session.get(SERVER_URL + LIST_TRAININGS_URL, params={'limit':-5}).json()['result'], 2, "Listing invalid limit")
        self.assertEqual(self.session.get(SERVER_URL + LIST_TRAININGS_URL, params={'limit':"abba"}).json()['result'], 2, "Listing invalid limit")
    def test_trainings_not_listing_other_users_lessons(self):
        (session2, lesson2Id) = createTestUserSetup("test2")
        createTraining(self.session, self.lessonId, DEF_BATCH_SIZE, DEF_TRAINING_REPETITIONS)
        createTraining(session2, lesson2Id, DEF_BATCH_SIZE, DEF_TRAINING_REPETITIONS)
        r1 = getTrainings(self.session).json()
        r2 = getTrainings(session2).json()
        self.assertEqual(len(r1['data']), 1, "Not listing others trainings")
        self.assertEqual(int(r1['data'][0]['lessonid']), self.lessonId, 'proper training get listed')
        self.assertEqual(len(r2['data']), 1, "Not listing others trainings")
        self.assertEqual(int(r2['data'][0]['lessonid']), lesson2Id, 'proper training get listed')

def getNextTrainingBatch(session, trainingId):
    return session.get(SERVER_URL + GET_NEXT_TRAINING_BATCH_URL, params={'trainingId':trainingId})

class TrainingBatchFetching(unittest.TestCase):
    def setUp(self):
        resetDb()
        (self.session, self.lessonId) = createTestUserSetup()
    def setUpTraining(self, entriesNum = 10):
        populateTestLesson(self.session, self.lessonId, 0, entriesNum)
        r = createTraining(self.session, self.lessonId, DEF_BATCH_SIZE, DEF_TRAINING_REPETITIONS).json()
        self.trainingId = r['startedTrainingId']
    def test_get_next_training_batch_method(self):
        r = self.session.get(SERVER_URL + GET_NEXT_TRAINING_BATCH_URL)
        self.assertEqual(r.status_code, HTTP_OK)
    def test_get_next_training_batch_on_empty_training_result_length(self):
        trainingId = createTraining(self.session, self.lessonId, DEF_BATCH_SIZE, DEF_TRAINING_REPETITIONS).json()['startedTrainingId']
        r = getNextTrainingBatch(self.session, trainingId).json()
        self.assertEqual(r['result'], 0, "Successfully fetched next training batch")
        self.assertEqual(len(r['data']), 0, "Fetch batch for empty training")
    def test_get_next_training_batch_on_not_full_training_result_length(self):
        self.setUpTraining(3)
        r = getNextTrainingBatch(self.session, self.trainingId).json()
        self.assertEqual(r['result'], 0, "Successfully fetched next training batch")
        self.assertEqual(len(r['data']), 3, "Fetch not full batch")
    def test_get_next_training_batch_on_full_training_result_length(self):
        self.setUpTraining()
        r = getNextTrainingBatch(self.session, self.trainingId).json()
        self.assertEqual(r['result'], 0, "Successfully fetched next training batch")
        self.assertEqual(len(r['data']), 5, "Fetch full batch")
    def test_get_next_training_batch_on_training_with_diffrent_batch_size(self):
        populateTestLesson(self.session, self.lessonId, 0, 10)
        trainingId = createTraining(self.session, self.lessonId, 9, DEF_TRAINING_REPETITIONS).json()['startedTrainingId']
        r = getNextTrainingBatch(self.session, trainingId).json()
        self.assertEqual(r['result'], 0, "Successfully fetched next training batch")
        self.assertEqual(len(r['data']), 9, "Fetch batch for non standard batch size")
    def test_get_next_training_batch_result_format(self):
        self.setUpTraining()
        r = getNextTrainingBatch(self.session, self.trainingId).json()
        self.assertIsNone(r.get('modifiedSinceLastSeen'))
        entry = r['data'][0]
        self.assertIsNotNone(entry['id'])
        self.assertIsNotNone(entry['question'])
        self.assertIsNotNone(entry['answer'])
        answer = entry['answer']
        question = entry['question']
        self.assertEqual(answer[1:], question[1:])
    def test_no_session_get_next_training_batch(self):
        self.setUpTraining()
        r = getNextTrainingBatch(requests, self.trainingId).json()
        self.assertEqual(r['result'], 1)
    def test_get_next_training_batch_missing_request_param(self):
        self.setUpTraining()
        r = self.session.get(SERVER_URL + GET_NEXT_TRAINING_BATCH_URL).json()
        self.assertEqual(r['result'], 2)
    def test_get_next_training_batch_on_non_existing_training(self):
        self.setUpTraining()
        r = getNextTrainingBatch(self.session, self.trainingId + 1).json()
        self.assertEqual(r['result'], 3)
    def test_get_next_training_batch_missing_privileges(self):
        self.setUpTraining()
        (session2, _) = createTestUserSetup('test2')
        r = getNextTrainingBatch(session2, self.trainingId).json()
        self.assertEqual(r['result'], 3)
    def test_get_next_training_batch_after_base_lesson_removal(self):
        self.setUpTraining()
        removeLesson(self.session, self.lessonId)
        r = getNextTrainingBatch(self.session, self.trainingId).json()
        self.assertEqual(r['result'], 4)
    def test_get_next_training_batch_after_base_lesson_entry_addition(self):
        self.setUpTraining(0)
        addLessonEntry(self.session, self.lessonId, "q1", "a1")
        r = getNextTrainingBatch(self.session, self.trainingId).json()
        self.assertEqual(r.get('modifiedSinceLastSeen'), True)
        self.assertEqual(len(r['data']), 1)
        self.assertEqual(r['data'][0]['question'], 'q1')
        self.assertEqual(r['data'][0]['answer'], 'a1')
    def test_get_next_training_batch_after_base_lesson_entry_removal(self):
        entryId = addLessonEntry(self.session, self.lessonId, "q1", "a1")
        self.setUpTraining(0)
        assert removeLessonEntry(self.session, entryId)
        r = getNextTrainingBatch(self.session, self.trainingId).json()
        self.assertEqual(r.get('modifiedSinceLastSeen'), True)
        self.assertEqual(len(r['data']), 0)
    def test_get_next_training_batch_after_base_lesson_entry_modification(self):
        entryId = addLessonEntry(self.session, self.lessonId, "q1", "a1")
        self.setUpTraining(0)
        assert modifyLessonEntry(self.session, entryId, 'newQ1', 'newA1')
        r = getNextTrainingBatch(self.session, self.trainingId).json()
        self.assertEqual(r.get('modifiedSinceLastSeen'), True)
        self.assertEqual(len(r['data']), 1)
        self.assertEqual(r['data'][0]['question'], 'newQ1')
        self.assertEqual(r['data'][0]['answer'], 'newA1')

def submitTrainingResult(session, trainingId, correctAnswers, wrongAnswers):
    return session.post(SERVER_URL + SUBMIT_TRAINING_RESULT_URL, data={'trainingId':trainingId, 'correctAnswers':json.dumps(correctAnswers), 'wrongAnswers':json.dumps(wrongAnswers)})

class TrainingResultSubmitting(unittest.TestCase):
    def setUp(self):
        resetDb()
        (self.session, self.lessonId) = createTestUserSetup()
    def setUpTraining(self, batchSize = DEF_BATCH_SIZE, trainingRepetitions = DEF_TRAINING_REPETITIONS, entriesNum = 10):
        populateTestLesson(self.session, self.lessonId, 0, entriesNum)
        r = createTraining(self.session, self.lessonId, batchSize, trainingRepetitions).json()
        self.trainingId = r['startedTrainingId']
    def getNextBatchIds(self):
        entries = getNextTrainingBatch(self.session, self.trainingId).json()['data']
        return [int(el['id']) for el in entries]
    def test_submit_training_result_method(self):
        self.setUpTraining(5, 1, 10)
        r = submitTrainingResult(self.session, self.trainingId, self.getNextBatchIds(), [])
        self.assertEqual(r.status_code, HTTP_OK)
    def test_no_session_submit_training_result(self):
        self.setUpTraining()
        r = submitTrainingResult(requests, self.trainingId, self.getNextBatchIds(), []).json()
        self.assertEqual(r['result'], 1)
    def test_submit_training_result_missing_param(self):
        self.setUpTraining()
        ids = self.getNextBatchIds()
        r1 = self.session.post(SERVER_URL + SUBMIT_TRAINING_RESULT_URL, data={'correctAnswers':json.dumps(ids), 'wrongAnswers':json.dumps([])}).json()
        r2 = self.session.post(SERVER_URL + SUBMIT_TRAINING_RESULT_URL, data={'trainingId':self.trainingId, 'wrongAnswers':json.dumps([])}).json()
        r3 = self.session.post(SERVER_URL + SUBMIT_TRAINING_RESULT_URL, data={'trainingId':self.trainingId, 'correctAnswers':json.dumps(ids)}).json()
        self.assertEqual(r1['result'], 2)
        self.assertEqual(r2['result'], 2)
        self.assertEqual(r3['result'], 2)
    def test_submit_training_result_correctAnswers_invalid_format(self):
        self.setUpTraining()
        r = self.session.post(SERVER_URL + SUBMIT_TRAINING_RESULT_URL, data={'trainingId':self.trainingId, 'correctAnswers':'abba', 'wrongAnswers':'[]'}).json()
        self.assertEqual(r['result'], 3)
    def test_submit_training_result_wrongAnswers_invalid_format(self):
        self.setUpTraining()
        r = self.session.post(SERVER_URL + SUBMIT_TRAINING_RESULT_URL, data={'trainingId':self.trainingId, 'correctAnswers':'[]', 'wrongAnswers':'abba'}).json()
        self.assertEqual(r['result'], 4)
    def test_submit_training_result_non_existing_training(self):
        self.setUpTraining()
        ids = self.getNextBatchIds()
        r = submitTrainingResult(self.session, self.trainingId + 1, ids, []).json()
        self.assertEqual(r['result'], 5)
    def test_submit_training_result_missing_priveleges(self):
        (session2, _) = createTestUserSetup('test2')
        self.setUpTraining()
        ids = self.getNextBatchIds()
        r = submitTrainingResult(session2, self.trainingId, ids, []).json()
        self.assertEqual(r['result'], 5)
    def test_submit_training_result_base_lesson_after_modification(self):
        self.setUpTraining()
        ids = self.getNextBatchIds()
        modifyLessonEntry(self.session, ids[0], 'newQ', 'newA')
        r = submitTrainingResult(self.session, self.trainingId, ids, []).json()
        self.assertEqual(r['result'], 6)
    def test_submit_training_result_invalid_id(self):
        self.setUpTraining()
        ids = self.getNextBatchIds()
        r1 = submitTrainingResult(self.session, self.trainingId, ids, [ids[0] + 1000]).json()
        r2 = submitTrainingResult(self.session, self.trainingId, [ids[0] + 1000], ids).json()
        self.assertEqual(r1['result'], 7)
        self.assertEqual(r2['result'], 7)
    def test_submit_training_result_already_finished_entries(self):
        self.setUpTraining(1, 1, 1)
        ids = self.getNextBatchIds()
        submitTrainingResult(self.session, self.trainingId, ids, [])
        r = submitTrainingResult(self.session, self.trainingId, ids, []).json()
        self.assertEqual(r['result'], 7)
    def test_submit_training_result_lesson_preogress(self):
        self.setUpTraining(4, 1, 6)
        ids = self.getNextBatchIds()
        submitTrainingResult(self.session, self.trainingId, ids, [])
        ids = self.getNextBatchIds()
        self.assertEqual(len(ids), 2)
    def test_submit_training_result_lesson_progress2(self):
        self.setUpTraining(4, 2, 6)
        ids = self.getNextBatchIds()
        assert submitTrainingResult(self.session, self.trainingId, ids, []).json()['result'] == 0
        ids = self.getNextBatchIds()
        self.assertEqual(len(ids), 4)
        assert submitTrainingResult(self.session, self.trainingId, ids, []).json()['result'] == 0
        ids = self.getNextBatchIds()
        self.assertEqual(len(ids), 2)
    def test_sumbit_training_result_lesson_wrong_answer(self):
        self.setUpTraining(4, 2, 6)
        ids = self.getNextBatchIds()
        submitTrainingResult(self.session, self.trainingId, ids, [])
        submitTrainingResult(self.session, self.trainingId, [], ids)
        submitTrainingResult(self.session, self.trainingId, ids, [])
        ids = self.getNextBatchIds()
        self.assertEqual(len(ids), 4)

def endTraining(session, trainingId):
    return session.post(SERVER_URL + END_TRAINING_URL, data={'trainingId':trainingId})

class TrainingEnding(unittest.TestCase):
    def setUp(self):
        resetDb()
        (self.session, self.lessonId) = createTestUserSetup()
        populateTestLesson(self.session, self.lessonId, 0, 1)
        r = createTraining(self.session, self.lessonId, DEF_BATCH_SIZE, DEF_TRAINING_REPETITIONS).json()
        self.trainingId = r['startedTrainingId']
    def test_end_training_method(self):
        r = endTraining(self.session, self.trainingId)
        self.assertEqual(r.status_code, HTTP_OK)
    def test_no_session_end_training(self):
        r = endTraining(requests, self.trainingId).json()
        self.assertEqual(r['result'], 1)
    def test_end_training_missing_request_param(self):
        r = self.session.post(SERVER_URL + END_TRAINING_URL).json()
        self.assertEqual(r['result'], 2)
    def test_end_training_invalid_trainingId_param(self):
        r = endTraining(self.session, "abba").json()
        self.assertEqual(r['result'], 3)
    def test_end_non_existing_training(self):
        r = endTraining(self.session, self.trainingId + 1).json()
        self.assertEqual(r['result'], 4)
    def test_end_training_with_missing_priveleges(self):
        (session2, _) = createTestUserSetup('test2')
        r = endTraining(session2, self.trainingId).json()
        self.assertEqual(r['result'], 4)
    def test_successful_end_training(self):
        r = endTraining(self.session, self.trainingId).json()
        self.assertEqual(r['result'], 0)
        r2 = getTrainings(self.session).json()
        self.assertEqual(len(r2['data']), 0)


if __name__ == '__main__':
    unittest.main()
