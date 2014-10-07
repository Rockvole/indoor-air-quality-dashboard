#include <cmath>

class ShinyeiPPD42NS {
  public:
    ShinyeiPPD42NS(float sampling_interval_ms) {
	  _sampling_interval_ms = sampling_interval_ms;
	  _is_sampling_complete = false;
	}
    void startSampling(unsigned long stms);
    float getConcentration(unsigned long duration, unsigned long current_time_ms);
    bool isSamplingComplete();
  private:
    ShinyeiPPD42NS() { }
    unsigned long _start_time_ms;
    unsigned long _low_pulse_occupancy;	
    float _sampling_interval_ms;
    float _concentration;
    bool _is_sampling_complete;
};
