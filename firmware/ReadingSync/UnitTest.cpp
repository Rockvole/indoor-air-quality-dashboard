#include "ReadingSync.h"

#include <ctime>
#include <iostream>

#define C_MINS_BETWEEN_READINGS 60
#define C_TEST_SECS 1409069240
#define C_TEST_5AM  1409029200

int basic_tests();
int loop_tests();

int main()
{
  basic_tests();
  loop_tests();
}

int basic_tests() {
  ReadingSync rs (C_MINS_BETWEEN_READINGS, time(0));
  std::cout << "start of day =" << rs.mins_between_readings << "\n";
  std::cout << "start of day =" << rs.getStartOfDayUnixTime(C_TEST_SECS) << "\n";
  std::cout << "is time to take reading =" << rs.isTimeToTakeReading(C_TEST_SECS) << "\n";

  if(rs.getStartOfDayUnixTime(C_TEST_SECS)!=1409011200) {
	  std::cout << "Error start of day calculation incorrect\n";
	  return(0);
  }
  if(rs.isTimeToTakeReading(C_TEST_SECS)) {
	  std::cout << "Not time to take a reading\n";
	  return(0);	  
  }
  std::cout << "next send mins =" << rs.next_send_mins << "\n";  
  if(!rs.isTimeToTakeReading(C_TEST_5AM)) {
	  std::cout << "Not on time\n";
	  return(0);	  
  } 
  std::cout << "next send mins =" << rs.next_send_mins << "\n";  
  if(!rs.isTimeToSendReading(C_TEST_5AM + (C_MINS_BETWEEN_READINGS * 60))) {
	  std::cout << "Too long to send reading\n";
	  return(0);	  
  }
  rs.setReadingSent();
  std::cout << "next send mins =" << rs.next_send_mins << "\n\n"; 	
  
  return(0);
}

int loop_tests() {
  ReadingSync rs (C_MINS_BETWEEN_READINGS, time(0));
  int send_count=0;	
  for(int curr_secs=0;curr_secs<7200;curr_secs=curr_secs+30) {
	bool itttr = rs.isTimeToTakeReading(C_TEST_5AM + curr_secs);
	if(!itttr) {
	  if(rs.isTimeToSendReading(C_TEST_5AM + curr_secs)) {
		rs.setReadingSent();  	  
	    send_count++;
	  }
	}
    //std::cout << "itttr(" << curr_secs << ")=" << itttr << "\n";
    if(curr_secs==0) {
	  if(itttr!=1) {
		std::cout << "Must be time to take reading at 5am\n";
	    return(0);		
      }
	} else if(curr_secs==3600) {
	  if(itttr!=1) {
	    std::cout << "Must be time to take reading at 6am\n";
	    return(0);		
      }
	} else {
	  if(itttr!=0) std::cout << "Must not be time to take reading\n";
	}
  }	
  if(send_count!=2) {
	std::cout << "We must have sent reading exactly twice\n";
	return(0);		
  }
  return(0);	
}
