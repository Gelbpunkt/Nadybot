DROP TABLE IF EXISTS ClusterImplantMap;
CREATE TABLE ClusterImplantMap (
	ImplantTypeID INT NOT NULL,
	ClusterID INT NOT NULL,
	ClusterTypeID INT NOT NULL
);
INSERT INTO ClusterImplantMap (ImplantTypeID, ClusterID, ClusterTypeID) VALUES
(1,8,3),
(1,9,1),
(1,12,1),
(1,18,2),
(1,20,2),
(1,21,1),
(1,26,3),
(1,34,2),
(1,35,2),
(1,37,2),
(1,38,3),
(1,40,1),
(1,44,2),
(1,49,2),
(1,50,1),
(1,51,2),
(1,53,2),
(1,56,2),
(1,57,2),
(1,60,1),
(1,62,2),
(1,63,1),
(1,64,2),
(1,66,2),
(1,68,3),
(1,72,2),
(1,73,1),
(1,75,1),
(1,79,1),
(1,81,2),
(1,82,3),
(1,83,3),
(1,84,2),
(1,85,2),
(1,86,1),
(2,10,3),
(2,13,3),
(2,18,3),
(2,20,3),
(2,22,2),
(2,26,2),
(2,31,3),
(2,37,3),
(2,38,2),
(2,40,3),
(2,41,3),
(2,43,3),
(2,44,3),
(2,45,3),
(2,51,3),
(2,52,2),
(2,53,3),
(2,54,3),
(2,56,1),
(2,57,3),
(2,61,3),
(2,62,3),
(2,63,3),
(2,64,3),
(2,66,3),
(2,67,2),
(2,71,1),
(2,72,3),
(2,79,3),
(2,80,1),
(2,81,3),
(2,82,1),
(2,83,1),
(2,84,3),
(2,85,3),
(2,86,2),
(2,112,3),
(3,21,2),
(3,37,1),
(3,38,1),
(3,56,3),
(3,61,1),
(3,62,1),
(3,63,2),
(3,82,2),
(3,83,2),
(3,84,1),
(3,85,1),
(3,91,3),
(3,92,3),
(3,110,2),
(4,4,1),
(4,6,1),
(4,10,2),
(4,11,3),
(4,15,1),
(4,22,3),
(4,27,3),
(4,36,2),
(4,41,2),
(4,42,3),
(4,43,1),
(4,47,3),
(4,48,1),
(4,51,1),
(4,52,3),
(4,61,2),
(4,71,3),
(4,72,1),
(4,76,3),
(4,77,1),
(4,93,1),
(4,109,1),
(4,112,1),
(5,5,1),
(5,6,2),
(5,7,1),
(5,10,1),
(5,11,2),
(5,14,1),
(5,17,3),
(5,19,3),
(5,22,1),
(5,24,1),
(5,25,2),
(5,27,1),
(5,28,1),
(5,30,3),
(5,33,1),
(5,36,1),
(5,42,2),
(5,43,2),
(5,46,1),
(5,47,2),
(5,52,1),
(5,59,1),
(5,65,3),
(5,71,2),
(5,74,1),
(5,76,1),
(5,110,3),
(6,6,3),
(6,7,3),
(6,11,1),
(6,24,3),
(6,25,3),
(6,27,2),
(6,28,2),
(6,36,3),
(6,42,1),
(6,46,2),
(6,47,1),
(6,70,1),
(6,76,2),
(6,78,3),
(6,88,1),
(6,91,1),
(6,92,1),
(6,93,3),
(6,103,1),
(6,104,1),
(6,107,1),
(6,108,1),
(6,109,3),
(6,112,2),
(7,7,2),
(7,21,3),
(7,24,2),
(7,25,1),
(7,28,3),
(7,39,2),
(7,46,3),
(7,58,3),
(7,75,3),
(7,87,1),
(7,88,2),
(7,89,1),
(7,90,1),
(7,92,2),
(7,101,2),
(7,102,2),
(7,105,2),
(7,106,2),
(8,4,2),
(8,5,2),
(8,12,2),
(8,14,3),
(8,15,2),
(8,17,1),
(8,41,1),
(8,58,1),
(8,59,2),
(8,65,2),
(8,77,2),
(8,78,1),
(8,88,3),
(8,89,3),
(8,90,3),
(8,110,1),
(8,111,3),
(9,45,2),
(9,49,3),
(9,50,3),
(9,54,1),
(9,55,2),
(9,68,1),
(9,69,2),
(9,70,2),
(9,94,2),
(9,95,2),
(9,96,2),
(9,97,2),
(9,98,2),
(9,99,2),
(9,100,2),
(9,101,1),
(9,102,1),
(9,103,3),
(9,104,3),
(9,105,1),
(9,106,1),
(9,107,3),
(9,108,3),
(10,19,1),
(10,29,3),
(10,30,2),
(10,31,1),
(10,39,1),
(10,66,1),
(10,80,2),
(10,93,2),
(10,101,3),
(10,102,3),
(10,103,2),
(10,104,2),
(10,105,3),
(10,106,3),
(10,107,2),
(10,108,2),
(10,109,2),
(10,111,2),
(11,2,3),
(11,3,3),
(11,4,3),
(11,5,3),
(11,9,3),
(11,12,3),
(11,14,2),
(11,15,3),
(11,16,3),
(11,17,2),
(11,29,1),
(11,32,3),
(11,33,3),
(11,34,3),
(11,35,3),
(11,44,1),
(11,48,3),
(11,55,1),
(11,58,2),
(11,59,3),
(11,65,1),
(11,69,1),
(11,74,3),
(11,77,3),
(11,78,2),
(11,87,2),
(11,89,2),
(11,90,2),
(11,111,1),
(12,2,2),
(12,3,2),
(12,8,2),
(12,13,1),
(12,16,2),
(12,32,1),
(12,33,2),
(12,45,1),
(12,49,1),
(12,50,2),
(12,54,2),
(12,55,3),
(12,60,3),
(12,67,3),
(12,68,2),
(12,69,3),
(12,70,3),
(12,73,3),
(12,75,2),
(12,87,3),
(12,91,2),
(12,94,1),
(12,95,1),
(12,96,1),
(12,97,1),
(12,98,1),
(12,99,1),
(12,100,1),
(13,2,1),
(13,3,1),
(13,8,1),
(13,9,2),
(13,13,2),
(13,16,1),
(13,18,1),
(13,19,2),
(13,20,1),
(13,26,1),
(13,29,2),
(13,30,1),
(13,31,2),
(13,32,2),
(13,34,1),
(13,35,1),
(13,39,3),
(13,40,2),
(13,48,2),
(13,53,1),
(13,57,1),
(13,60,2),
(13,64,1),
(13,67,1),
(13,73,2),
(13,74,2),
(13,79,2),
(13,80,3),
(13,81,1),
(13,86,3),
(13,94,3),
(13,95,3),
(13,96,3),
(13,97,3),
(13,98,3),
(13,99,3),
(13,100,3),
(1,130,3),
(7,130,2),
(11,130,1);
