#include "ReadingSync.h"

#include <ctime>
#include <iostream>

#define C_MINS_BETWEEN_READINGS 60
#define C_SECS_TO_PRE_HEAT 300

#define C_TEST_BEFORE   1409067500 // Tue, 26 Aug 2014 15:38:20 GMT - before pre-heat
#define C_TEST_PRE_HEAT 1409068500 // Tue, 26 Aug 2014 15:55:00 GMT - pre-heat starting
#define C_TEST_SAMPLING 1409068800 // Tue, 26 Aug 2014 16:00:00 GMT
#define C_TEST_AFTER    1409068802 // Tue, 26 Aug 2014 16:00:02 GMT

#define C_TEST_5AM      1409029200 // Tue, 26 Aug 2014 05:00:00 GMT

int basic_tests();
int loop_tests();
int one_min_tests();

int main()
{
  std::cout << "------------------------------------------ BASIC TESTS\n";
  basic_tests();
  std::cout << "------------------------------------------ LOOP TESTS\n";
  loop_tests();
  std::cout << "------------------------------------------ ONE MIN TESTS\n";
  one_min_tests();
}

int basic_tests() {
  ReadingSync rs (C_MINS_BETWEEN_READINGS, C_SECS_TO_PRE_HEAT, time(0));
  std::cout << "secs between readings =" << rs.secs_between_readings << "\n";
  std::cout << "start of day =" << rs.getStartOfDayUnixTime(C_TEST_AFTER) << "\n";
  std::cout << "is time to take samples =" << (rs.getStage(C_TEST_AFTER)==rs.SAMPLING) << "\n";
  std::cout << "--------------------------------------------------------------------------\n";

  if(rs.getStartOfDayUnixTime(C_TEST_BEFORE)!=1409011200) {
      std::cout << "Error start of day calculation incorrect\n";
      return(0);
  }
  if(rs.getStage(C_TEST_BEFORE)!=rs.CONTINUE) {
      std::cout << "We should be continuing until we reach pre-heat time, stage=" << rs.getStage(C_TEST_BEFORE) << "\n";
      return(0);          
  }
 
  if(rs.getStage(C_TEST_PRE_HEAT)!=rs.PRE_HEATING) {
      std::cout << "We should be in pre-heat stage, stage=" << rs.getStage(C_TEST_PRE_HEAT) << "\n";
      return(0);          
  }  
  if(rs.getStage(C_TEST_SAMPLING)!=rs.SAMPLING) {
      std::cout << "Not time to take samples, stage=" << rs.getStage(C_TEST_SAMPLING) << "\n";
      return(0);          
  }
  rs.setSamplingComplete();
  if(rs.getStage(C_TEST_AFTER)!=rs.CONTINUE) {
      std::cout << "Must be continue, stage=" << rs.getStage(C_TEST_AFTER) << "\n";
      return(0);          
  }  
  return(0);
}

int loop_tests() {
  ReadingSync rs (C_MINS_BETWEEN_READINGS, C_SECS_TO_PRE_HEAT, time(0));
  int send_count=0; 
  ReadingSync::Stage currStage;
  for(int curr_secs=0;curr_secs<3600;curr_secs++) { // Test for 1 hour
    if(curr_secs==1000) rs.startUserSampling(C_TEST_5AM + curr_secs);
    if(curr_secs==1600) rs.setSamplingComplete();            
    if(curr_secs==2000) rs.startCalibrating(C_TEST_5AM + curr_secs);
    if(curr_secs==2600) rs.setCalibratingComplete();      
    currStage=rs.getStage(C_TEST_5AM + curr_secs);

    std::cout << "time=" << (C_TEST_5AM + curr_secs) << "||curr_secs=" << curr_secs << "||stage=" << currStage << "\n";
    // ----------------------------------------------------------------- SCHEDULED SAMPLING    
    if(curr_secs>=0 && curr_secs<=100) {
      if(currStage!=rs.SAMPLING) {
        std::cout << "Must be time to take samples at 5am\n";
        return(0);      
      }
      if(curr_secs==100) rs.setSamplingComplete();
    } else if(curr_secs==3600) {
      if(currStage!=rs.SAMPLING) {
        std::cout << "Must be time to take samples at 6am\n";
        return(0);      
      }
    } else {
      if(currStage==rs.SAMPLING) std::cout << "Must not be time to take samples\n";
    }
    // ----------------------------------------------------------------- SEND READING    
    if(currStage==rs.SEND_READING) {
      send_count++;     
      rs.setReadingSent();        
    }   
    // ----------------------------------------------------------------- PRE-HEATING    
    if(curr_secs>=3300 && curr_secs<3600) {
        if(currStage!=rs.PRE_HEATING) {
          std::cout << "Must be time to Pre-heat\n";
          return(0);
        }    
    }
    // ----------------------------------------------------------------- USER SAMPLING
    if(curr_secs>=1000 && curr_secs<1300) {
        if(currStage!=rs.PRE_HEAT_USER_SAMPLING) {
            std::cout << "Must be time to Pre-Heat User Sampling " << curr_secs << "||stage=" << currStage;
            return(0);
        }    
    }    
    if(curr_secs>=1300 && curr_secs<1600) {
        if(currStage!=rs.USER_SAMPLING) {
            std::cout << "Must be time to User Sample " << curr_secs << "||stage=" << currStage;
            return(0);
        }    
    }         
    // ----------------------------------------------------------------- CALIBRATING    
    if(curr_secs>=2000 && curr_secs<2300) {
        if(currStage!=rs.PRE_HEAT_CALIBRATING) {
            std::cout << "Must be time to Pre-Heat Calibrate " << curr_secs << "||stage=" << currStage;
            return(0);
        }    
    }    
    if(curr_secs>=2300 && curr_secs<2600) {
        if(currStage!=rs.CALIBRATING) {
            std::cout << "Must be time to Calibrate " << curr_secs << "||stage=" << currStage;
            return(0);
        }    
    }     
  } // curr_secs loop
  if(send_count!=1) {
    std::cout << "We must have sent reading exactly once: count=" << send_count << "\n";
    return(0);      
  }
  return(0);    
}

int one_min_tests() {
  //              MINS_BETWEEN_READINGS  SECS_TO_PRE_HEAT      CURRENT_TIME (random seed)
  ReadingSync rs (1,                     1,                    time(0));
  int pre_heat_count=0;
  int send_count=0; 
  ReadingSync::Stage stage;  
  for(int curr_secs=0;curr_secs<3600;curr_secs++) {
	stage=rs.getStage(C_TEST_5AM + curr_secs);
  
	std::cout << "time=" << (C_TEST_5AM + curr_secs) << "||curr_secs=" << curr_secs << "||stage=" << stage << "\n";
	
	switch(stage) {
	  case ReadingSync::USER_SAMPLING:
	  case ReadingSync::SAMPLING:
		if(curr_secs % 60 == 2) { // On the 2nd Second
		  rs.setSamplingComplete();
	    }
	    break;
	  case ReadingSync::SEND_READING:
        send_count++;     
        rs.setReadingSent(); 
        break;	    
      case ReadingSync::CALIBRATING:
        break;
      case ReadingSync::PRE_HEAT_CALIBRATING:
      case ReadingSync::PRE_HEAT_USER_SAMPLING:
      case ReadingSync::PRE_HEATING: 
        if(rs.isFirstPreHeatLoop()) { // Take ambient temperature before pre-heating
          std::cout << "Pre-Heating" << "\n";
          pre_heat_count++; 
        }
        break;
      case ReadingSync::CONTINUE:
        break;
      default:
        break;
	}

  }
  if(pre_heat_count!=60) {
    std::cout << "We must have pre heating exactly 60X: count=" << send_count << "\n";
    return(0);      
  }  
  if(send_count!=60) {
    std::cout << "We must have sent reading exactly 60X: count=" << send_count << "\n";
    return(0);      
  }
  return(0);
}
