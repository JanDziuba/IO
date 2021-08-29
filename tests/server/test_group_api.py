from unittest.loader import defaultTestLoader
import requests
import unittest
from requests.api import request

from requests.sessions import session
from py_helpers import SERVER_URL, resetDb, createTestUserSetup, populateTestLesson, removeLesson, addLessonEntry, removeLessonEntry, modifyLessonEntry
import json

CREATE_GROUP_URL = 'server/createGroup.php'
UPDATE_GROUP_URL = 'server/updateGroup.php'
REMOVE_GROUP_URL = 'server/removeGroup.php'
LIST_GROUPS_URL = 'server/listGroups.php'
GROUP_JOIN_REQUEST_SEND_URL = 'server/sendGroupJoinReqest.php'
LIST_GROUP_JOIN_REQUESTS_URL = 'server/listGroupJoinRequests.php'
ACCEPT_GROUP_JOIN_REQUEST_URL = 'server/acceptGroupJoinRequest.php'
DENY_GROUP_JOIN_REQUEST_URL = 'server/denyGroupJoinRequest.php'
HTTP_OK = 200

'''
zasady nazywania grup w testach przyjmuję następujące:
długość między 3 a 32 znaki włącznie
zasady opisów grup w testach przyjmuję następujące:
długość od 0 do 300 znaków włącznie
zasady próśb dołączenia do grupy przyjmuję następujące:
długość od 0 do 300 znaków włącznie
'''

INVALID_GROUP_NAMES = ('aa', 'a'*33)
INVALID_GROUP_DESCRIPTIONS = ('a'*301)
INVALID_GROUP_JOIN_REQUESTS = ('a'*301)

INVALID_GROUPS_LISTING_LIMIT_PARAM = ('a', -2, 3.141592)
INVALID_GROUP_JOIN_REQUESTS_LIMIT_PARAM = ('a', -2, 3.141592)
INVALID_GROUP_ID_VALUES = ('a', 3.141592)

DEF_GROUP_NAME = 'testGroup'

'''
zasady banowania próśb dołączenia do grupy przyjmuję następujące:
dopóki poprzednia prośba jest aktywna nie można wysłać nowej prośby
po odrzuceniu pod rząd 3 próśb dołączenia do grupy bez zaakceptowania w międzyczasie
użytkownik nie może więcej wysłać prośby do danej grupy
'''


def createGroup(session, name = DEF_GROUP_NAME, description = ''):
    return session.post(SERVER_URL + CREATE_GROUP_URL, data={'name':name, 'description':description})

def createTestGroup(session, name = DEF_GROUP_NAME):
    return createGroup(session, name).json().get('createdGroupId')

def updateGroup(session, groupId, newName=None, newDescription=None):
    return session.post(SERVER_URL + UPDATE_GROUP_URL, data={'groupId':groupId, 'newName':newName, 'newDescription':newDescription})

def removeGroup(session, groupId):
    return session.post(SERVER_URL + REMOVE_GROUP_URL, data={'groupId':groupId})

def listGroups(session, limit = None):
    return session.get(SERVER_URL + LIST_GROUPS_URL, data={'limit':limit})

def sendGroupJoinRequest(session, groupId, requestText = None):
    return session.post(SERVER_URL + GROUP_JOIN_REQUEST_SEND_URL, data={'groupId':groupId, 'requestText':requestText})

def listGroupJoinRequests(session, groupId, limit = None):
    return session.get(SERVER_URL + LIST_GROUP_JOIN_REQUESTS_URL, data={'groupId':groupId, 'limit':limit})

def acceptGroupJoinRequest(session, joinRequestId):
    return session.post(SERVER_URL + ACCEPT_GROUP_JOIN_REQUEST_URL, data={'joinRequestId':joinRequestId})

def denyGroupJoinRequest(session, joinRequestId):
    return session.post(SERVER_URL + DENY_GROUP_JOIN_REQUEST_URL, data={'joinRequestId':joinRequestId})


class GroupCreation(unittest.TestCase):
    def setUp(self) -> None:
        super().setUp()
        resetDb()
        (self.session, self.lessonId) = createTestUserSetup()
    def test_group_creation_method(self):
        r = createGroup(self.session)
        self.assertEqual(r.status_code, HTTP_OK, "Group creation method existence")
    def test_successful_group_creation(self):
        result = createGroup(self.session).json()
        self.assertEqual(result['result'], 0, "Successful group creation should return result 0")
    def test_successful_group_creation_result_format(self):
        r = createGroup(self.session).json()
        self.assertIsNotNone(r.get('createdGroupId'))
    def test_group_creation_request_invalid_method(self):
        r = self.session.get(SERVER_URL + CREATE_GROUP_URL, params={'name':DEF_GROUP_NAME}).json()
        self.assertNotEqual(r['result'], 0)
    def test_no_session_group_creation_request(self):
        r = createGroup(requests).json()
        self.assertEqual(r['result'], 1, "Group creation request without valid session should return result 1")
    def test_missing_param_group_creation_request(self):
        r1 = self.session.post(SERVER_URL + CREATE_GROUP_URL, params={'name':DEF_GROUP_NAME}).json()
        r2 = self.session.post(SERVER_URL + CREATE_GROUP_URL, params={'description':'test'}).json()
        self.assertEqual(r1['result'], 2, "Group creation request missing required param should return result 2")
        self.assertEqual(r2['result'], 0, "Group creation request optional description param")
    def test_group_creation_request_invalid_name(self):
        for name in INVALID_GROUP_NAMES:
            r = createGroup(self.session, name).json()
            self.assertEqual(r['result'], 3, "Group creation request invalid name")
    def test_group_creation_request_invalid_description(self):
        for description in INVALID_GROUP_DESCRIPTIONS:
            r = createGroup(self.session, DEF_GROUP_NAME, description).json()
            self.assertEqual(r['result'], 4, "Group creation request invalid description")

class groupUpdate(unittest.TestCase):
    def setUp(self) -> None:
        super().setUp()
        resetDb()
        (self.session, self.lessonId) = createTestUserSetup()
        self.groupId = createTestGroup(self.session)
    def test_group_update_method(self):
        r = updateGroup(self.session, self.groupId, 'testGroup2')
        self.assertEqual(r.status_code, HTTP_OK, "Group update method existence")
    def test_successful_group_update(self):
        r1 = updateGroup(self.session, self.groupId, 'testGroup2').json()
        r2 = updateGroup(self.session, self.groupId, None, 'test').json()
        self.assertEqual(r1['result'], 0, "Successful group update should return result 0")
        self.assertEqual(r2['result'], 0, "Successful group update should return result 0")
    def test_group_update_request_invalid_method(self):
        r = self.session.get(SERVER_URL + UPDATE_GROUP_URL, params={'groupId':self.groupId, 'newName':'testGroup2'}).json()
        self.assertNotEqual(r['result'], 0)
    def test_no_session_group_update_request(self):
        r = updateGroup(requests, self.groupId, None, 'test').json()
        self.assertEqual(r['result'], 1, "Group update request without valid session")
    def test_missing_param_group_update_request(self):
        r1 = self.session.post(SERVER_URL + UPDATE_GROUP_URL, params={'newName':'testGroup2'}).json()
        r2 = self.session.post(SERVER_URL + UPDATE_GROUP_URL, params={'groupId':self.groupId}).json()
        r3 = self.session.post(SERVER_URL + UPDATE_GROUP_URL, params={'groupId':self.groupId, 'newName':'testGroup2'}).json()
        r4 = self.session.post(SERVER_URL + UPDATE_GROUP_URL, params={'groupId':self.groupId, 'newDescription':'test'}).json()
        self.assertEqual(r1['result'], 2, "Group update request missing required param groupId")
        self.assertEqual(r2['result'], 2, "Group update request missing all new? param")
        self.assertEqual(r3['result'], 0, "Group update request missing only optional newDescription param")
        self.assertEqual(r4['result'], 0, "Group update request missing only optional newName param")
    def test_missing_group_update_request(self):
        r = updateGroup(self.session, self.groupId + 1000, 'testGroup2').json()
        self.assertEqual(r['result'], 3, "Update missing group request")
    def test_missing_group_privileges_update_request(self):
        (session2, _) = createTestUserSetup(login='test2')
        r = updateGroup(session2, self.groupId, 'testGroup2').json()
        self.assertEqual(r['result'], 3, "Update group with missing priveleges")
    def test_group_update_request_invalid_name(self):
        for newName in INVALID_GROUP_NAMES:
            r = updateGroup(self.session, self.groupId, newName).json()
            self.assertEqual(r['result'], 4, "Group update request invalid name")
    def test_group_update_request_invalid_description(self):
        for newDescription in INVALID_GROUP_DESCRIPTIONS:
            r = updateGroup(self.session, self.groupId, newDescription=newDescription).json()
            self.assertEqual(r['result'], 4, "Group update request invalid description")

class groupRemoval(unittest.TestCase):
    def setUp(self) -> None:
        super().setUp()
        resetDb()
        (self.session, self.lessonId) = createTestUserSetup()
        self.groupId = createTestGroup(self.session)
    def test_group_removal_method(self):
        r = removeGroup(self.session, self.groupId)
        self.assertEqual(r.status_code, HTTP_OK, "Group removal method existence")
    def test_successful_group_removal(self):
        r = removeGroup(self.session, self.groupId).json()
        self.assertEqual(r['result'], 0, "Successful group removal should return result 0")
    def test_group_removal_request_invalid_method(self):
        r = self.session.get(SERVER_URL + REMOVE_GROUP_URL, params={'groupId':self.groupId}).json()
        self.assertNotEqual(r['result'], 0)
    def test_no_session_group_removal_request(self):
        r = removeGroup(requests, self.groupId).json()
        self.assertEqual(r['result'], 1, "Group removal request without valid session")
    def test_missing_param_group_removal_request(self):
        r = self.session.post(SERVER_URL + REMOVE_GROUP_URL, params={}).json()
        self.assertEqual(r['result'], 2, "Group removal request missing required param groupId")
    def test_missing_group_removal_request(self):
        r = removeGroup(self.session, self.groupId + 1000).json()
        self.assertEqual(r['result'], 3, "Remove missing group request")
    def test_missing_group_privileges_removal_request(self):
        (session2, _) = createTestUserSetup(login='test2')
        r = removeGroup(session2, self.groupId).json()
        self.assertEqual(r['result'], 3, "Remove group with missing priveleges")
    def test_removed_group_removal_request(self):
        r = removeGroup(self.session, self.groupId).json()
        self.assertEqual(r['result'], 0, "Successful group removal should return result 0")
        r = removeGroup(self.session, self.groupId).json()
        self.assertEqual(r['result'], 3, "Removal of removed group")

class OwnGroupsListing(unittest.TestCase):
    def setUp(self):
        resetDb()
        (self.session, self.lessonId) = createTestUserSetup()
        self.groupId = createTestGroup(self.session)
    def test_groups_listing_method(self):
        r = listGroups(self.session)
        self.assertEqual(r.status_code, HTTP_OK, "Groups listing method existence")
    def test_no_groups_listing_result_length(self):
        # creating user with no owned groups
        (session2, _) = createTestUserSetup('test2')
        r1 = listGroups(session2).json()
        self.assertEqual(r1['result'], 0, "Successful listing method result")
        self.assertEqual(len(r1['data']), 0, "Listing empty list")
    def test_one_group_listing_result_length(self):
        r2 = listGroups(self.session).json()
        self.assertEqual(r2['result'], 0, "Successful listing method result")
        self.assertEqual(len(r2['data']), 1, "Listing non-empty list")
    def test_many_groups_listing_result_length(self):
        for i in range(42):
            createTestGroup(self.session, 'testGroup' + i)
        # now, listing should return 43 groups
        r3 = listGroups(self.session).json()
        self.assertEqual(r3['result'], 0, "Successful listing method result")
        self.assertEqual(len(r3['data']), 43, "Listing non-empty list")
    def test_groups_listing_result_format(self):
        r1 = listGroups(self.session).json()
        self.assertEqual(r1['result'], 0, "Successful listing method result")
        self.assertEqual(len(r1['data']), 1, "Listing non-empty list")
        groupEntry = r1['data'][0]
        self.assertIsNotNone(groupEntry['id'])
        self.assertEqual(groupEntry['name'], DEF_GROUP_NAME, "Returned invalid group name")
        self.assertEqual(groupEntry['description'], '', "Returned invalid group description")
    def test_groups_listing_result_format2(self):
        groupId2 = createGroup(self.session, 'abcd', 'abcde').json()['createdGroupId']
        r1 = listGroups(self.session).json()
        self.assertEqual(r1['result'], 0, "Successful listing method result")
        self.assertEqual(len(r1['data']), 2, "Listing non-empty list")
        groupEntry1 = r1['data'][0]
        groupEntry2 = r1['data'][1]
        groupEntry =  groupEntry1 if groupEntry1['id'] == groupId2 else groupEntry2
        self.assertEqual(groupEntry['id'], groupId2)
        self.assertEqual(groupEntry['name'], 'abcd', "Returned invalid group name")
        self.assertEqual(groupEntry['description'], 'abcde', "Returned invalid group description")
    def test_groups_listing_invalid_method(self):
        r = self.session.post(SERVER_URL + LIST_GROUPS_URL).json()
        self.assertEqual(r['result'], 4, "Handling only GET http method")
    def test_no_session_groups_listing(self):
        r = listGroups(requests).json()
        self.assertEqual(r['result'], 1)
    def test_groups_listing_invalid_limit_param(self):
        for limit in INVALID_GROUPS_LISTING_LIMIT_PARAM:
            r = listGroups(self.session, limit).json()
            self.assertEqual(r['result'], 2, "Group listing request invalid limit param value")
    def test_trainings_listing_limit(self):
        for i in range(42):
            createTestGroup(self.session, 'testGroup' + i)
        self.assertEqual(len(listGroups(self.session, -1).json()['data']), 43, "Listing non-empty list unlimited")
        self.assertEqual(len(listGroups(self.session, 1).json()['data']), 1, "Listing non-empty list limited")
        self.assertEqual(len(listGroups(self.session, 20).json()['data']), 20, "Listing non-empty list limited")
        self.assertEqual(len(listGroups(self.session, 100).json()['data']), 43, "Listing non-empty list overlimited")
    def test_trainings_not_listing_other_users_groups(self):
        (session2, _) = createTestUserSetup("test2")
        groupId2 = createTestGroup(session2, 'group2')
        r1 = listGroups(self.session).json()
        r2 = listGroups(session2).json()
        self.assertEqual(len(r1['data']), 1, "Not listing others groups")
        self.assertEqual(int(r1['data'][0]['id']), self.groupId, 'proper group get listed')
        self.assertEqual(len(r2['data']), 1, "Not listing others groups")
        self.assertEqual(int(r2['data'][0]['id']), groupId2, 'proper group get listed')

class GroupJoinRequestSending(unittest.TestCase):
    def setUp(self):
        resetDb()
        (self.session, self.lessonId) = createTestUserSetup()
        (self.session2, _) = createTestUserSetup('test2')
        self.groupId = createTestGroup(self.session)
        '''
        Setup:
        there is one user (identified with self.session) owning a group
        and one (identified with self.session2) without a group
        '''
    def test_group_join_request_sending_method(self):
        r = sendGroupJoinRequest(self.session2, self.groupId)
        self.assertEqual(r.status_code, HTTP_OK, "Group join request sending method existence")
    def test_successful_group_join_request_sending_method(self):
        result = sendGroupJoinRequest(self.session2, self.groupId).json()
        self.assertEqual(result['result'], 0, "Successful group join request sending")
    def test_group_join_request_sending_request_invalid_http_method(self):
        r = self.session2.get(SERVER_URL + GROUP_JOIN_REQUEST_SEND_URL, params={'groupId':self.groupId}).json()
        self.assertNotEqual(r['result'], 0)
    def test_no_session_group_join_request_sending_request(self):
        r = sendGroupJoinRequest(requests, self.groupId).json()
        self.assertEqual(r['result'], 1, "Sending group join request without valid session")
    def test_missing_param_group_join_request_sending_request(self):
        r1 = self.session.post(SERVER_URL + GROUP_JOIN_REQUEST_SEND_URL, data={}).json()
        r2 = self.session.post(SERVER_URL + GROUP_JOIN_REQUEST_SEND_URL, data={'requestText':'help'}).json()
        r3 = self.session.post(SERVER_URL + GROUP_JOIN_REQUEST_SEND_URL, data={'groupId':self.groupId}).json()
        self.assertEqual(r1['result'], 2, "Group join request sending request missing required param groupId")
        self.assertEqual(r2['result'], 2, "Group join request sending request missing required param groupId")
        self.assertEqual(r3['result'], 0, "Group join request sending request missing optional param requestText") 
    def test_missing_group_group_join_request_sending(self):
        r = sendGroupJoinRequest(self.session2, self.groupId + 1000).json()
        self.assertEqual(r['result'], 3, "Group join request sending request missing group")
    def test_sending_group_join_request_request_invalid_request_text(self):
        for requestText in INVALID_GROUP_JOIN_REQUESTS:
            r = sendGroupJoinRequest(self.session2, self.groupId, requestText)
            self.assertEqual(r['result'], 4, "Group join request sending request invalid request text")
    def test_group_join_request_sending_request_to_group_one_owns(self):
        r = sendGroupJoinRequest(self.session, self.groupId).json()
        self.assertEqual(r['result'], 5, "Sending group join request to group one already owns")
    def test_group_join_request_sending_to_group_with_pending_request(self):
        sendGroupJoinRequest(self.session2, self.groupId).json()
        r = sendGroupJoinRequest(self.session2, self.groupId).json()
        self.assertEqual(r['result'], 6, "Sending group join request to group with pending request")
# brakuje sprawdzenia banowania i dołączania do grupy, do której się już należy ale nie posiada

class GroupJoinRequestsListing(unittest.TestCase):
    def setUp(self):
        resetDb()
        (self.session, self.lessonId) = createTestUserSetup()
        self.groupId = createTestGroup(self.session)
        (self.session2, _) = createTestUserSetup('test2')
    def test_group_join_requests_listing_method(self):
        r = listGroupJoinRequests(self.session, self.groupId)
        self.assertEqual(r.status_code, HTTP_OK, "Group join requests listing method existence")
    def test_no_group_join_requests_listing_result_length(self):
        r1 = listGroupJoinRequests(self.session, self.groupId).json()
        self.assertEqual(r1['result'], 0, "Successful listing method result")
        self.assertEqual(len(r1['data']), 0, "Listing empty list")
    def test_one_group_join_request_listing_result_length(self):
        self.assertEqual(sendGroupJoinRequest(self.session2, self.groupId).json()['result'], 0, 'creating test group join request')
        r2 = listGroupJoinRequests(self.session, self.groupId).json()
        self.assertEqual(r2['result'], 0, "Successful listing method result")
        self.assertEqual(len(r2['data']), 1, "Listing non-empty list")
    def test_many_group_join_requests_listing_result_length(self):
        for i in range(12):
            (session, _) = createTestUserSetup('ttt' + i)
            sendGroupJoinRequest(session, self.groupId)
        # now, listing should return 12 group join requests
        r3 = listGroupJoinRequests(self.session, self.groupId).json()
        self.assertEqual(r3['result'], 0, "Successful listing method result")
        self.assertEqual(len(r3['data']), 12, "Listing non-empty list")
    def test_group_join_requests_listing_result_format(self):
        self.assertEqual(sendGroupJoinRequest(self.session2, self.groupId, 'test description').json()['result'], 0, 'creating test group join request')
        r1 = listGroupJoinRequests(self.session, self.groupId)
        self.assertEqual(r1['result'], 0, "Successful listing method result")
        self.assertEqual(len(r1['data']), 1, "Listing non-empty list")
        groupJoinRequestEntry = r1['data'][0]
        self.assertIsNotNone(groupJoinRequestEntry['id'])
        self.assertEqual(groupJoinRequestEntry['description'], 'test description', "Returned invalid group join request description")
        self.assertEqual(groupJoinRequestEntry['username'], 'test2', "Returned invalid group join request sender login")
    def test_group_join_requests_listing_invalid_http_method(self):
        r = self.session.post(SERVER_URL + LIST_GROUP_JOIN_REQUESTS_URL, data={'groupId':self.groupId}).json()
        self.assertEqual(r['result'], 7, "Handling only GET http method")
    def test_no_session_group_join_requests_listing(self):
        r = listGroupJoinRequests(requests, self.groupId)
        self.assertEqual(r['result'], 1)
    def test_missing_param_group_join_request_listing(self):
        r1 = self.session.post(SERVER_URL + LIST_GROUP_JOIN_REQUESTS_URL, data={}).json()
        r2 = self.session.post(SERVER_URL + LIST_GROUP_JOIN_REQUESTS_URL, data={'limit':-1}).json()
        r3 = self.session.post(SERVER_URL + LIST_GROUP_JOIN_REQUESTS_URL, data={'groupId':self.groupId}).json()
        self.assertEqual(r1['result'], 2, "Group join request listing request missing required param groupId")
        self.assertEqual(r2['result'], 2, "Group join request listing request missing required param groupId")
        self.assertEqual(r3['result'], 0, "Group join request listing request missing optional param limit")
    def test_invalid_group_id_group_join_requests_listing(self):
        for groupId in INVALID_GROUP_ID_VALUES:
            r = listGroupJoinRequests(self.session, groupId).json()
            self.assertEqual(r['result'], 3, "Group listing request invalid groupId param value")
    def test_group_join_requests_listing_invalid_limit_param(self):
        for limit in INVALID_GROUP_JOIN_REQUESTS_LIMIT_PARAM:
            r = listGroupJoinRequests(self.session, self.groupId, limit).json()
            self.assertEqual(r['result'], 4, "Group listing request invalid limit param value")
    def test_missing_group_group_join_requests_listing(self):
        r = listGroupJoinRequests(self.session, self.groupId + 1000).json()
        self.assertEqual(r['result'], 5, "Group which we try to list request for is missing")
    def test_missing_priveleges_group_join_requests_listing(self):
        r = listGroupJoinRequests(self.session2, self.groupId).json()
        self.assertEqual(r['result'], 5, "Group join requests listing missing priveleges")
    def test_group_join_requests_listing_limit(self):
        for i in range(12):
            (session, _) = createTestUserSetup('ttt' + i)
            sendGroupJoinRequest(session, self.groupId)
        # now, listing should return 12 group join requests
        self.assertEqual(len(listGroupJoinRequests(self.session, self.groupId, -1).json()['data']), 12, "Listing non-empty list unlimited")
        self.assertEqual(len(listGroupJoinRequests(self.session, self.groupId, 1).json()['data']), 1, "Listing non-empty list limited")
        self.assertEqual(len(listGroupJoinRequests(self.session, self.groupId, 10).json()['data']), 10, "Listing non-empty list limited")
        self.assertEqual(len(listGroupJoinRequests(self.session, self.groupId, 20).json()['data']), 12, "Listing non-empty list overlimited")
    def test_group_join_requests_listing_only_selected_group_requests(self):
        groupId2 = createTestGroup(self.session2, 'group2')
        sendGroupJoinRequest(self.session, groupId2, 'help')
        sendGroupJoinRequest(self.session2, self.groupId)
        r1 = listGroupJoinRequests(self.session, self.groupId).json()
        r2 = listGroupJoinRequests(self.session2, groupId2).json()
        self.assertEqual(len(r1['data']), 1, "Not listing others groups")
        self.assertEqual(int(r1['data'][0]['username']), 'test2', 'proper group get listed')
        self.assertEqual(len(r2['data']), 1, "Not listing others groups")
        self.assertEqual(int(r2['data'][0]['description']), 'help', 'proper group get listed')

if __name__ == '__main__':
    unittest.main()
