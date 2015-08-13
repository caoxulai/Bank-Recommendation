clc;
% clear all;
tic;
% load('C:\KK\Android\Product Recommendation\server\matlab\clustering_result.mat');
load('C:\KK\Android\Product Recommendation\server\matlab\Ck.mat');

[m,n] = size(Ck);
stats = zeros(30,17,n);

for i = 1:n
    for k = 1:m
        stats(Ck(k,i),l_to_i(label(k)),i) = stats(Ck(k,i),l_to_i(label(k)),i) + 1;
    end
end

 
S = sum(stats(:,:,1));
stats_percent = stats; 
for i = 1:n
    stats_percent(:,:,i) = stats_percent(:,:,i)./ repmat(S,size(stats_percent,1),1);
end
stats_percent = round(stats_percent*100);

max_in_cluster = [];
for i = 1:n
    max_in_cluster = [max_in_cluster; max(stats_percent(:,:,i)')];
end
max_in_cluster = max_in_cluster';
for i = 1:n
    matrix_max_percent(:,:,i)=[max_in_cluster(:,i)'; stats_percent(:,:,i)']';
end
 

matrix_variance_occrrence = [];
num_rows = size(stats,1);
for i = 1:n
    for j = 1:num_rows
        row = stats(j, :, i);
        [max1,max_idx] = max(row);
        row(max_idx) =[];
        [max2,max_idx] = max(row);
        row(max_idx) =[];
        [max3,max_idx] = max(row);
        row(max_idx) =[];
        
        vari = round(((max1 - max2)/max1 * 10).^2 +((max1 - max3)/max1 * 10).^2);
        matrix_variance_occrrence(j,:,i)=[vari stats(j,:,i)];
    end
end

matrix_variance_max_percent = [];
num_rows = size(stats,1);
for i = 1:n
    for j = 1:num_rows
        row = stats(j, :, i);
        [max1,max_idx] = max(row);
        row(max_idx) =[];
        [max2,max_idx] = max(row);
        row(max_idx) =[];
        [max3,max_idx] = max(row);
        row(max_idx) =[];
        
        vari = round(((max1 - max2)/max1 * 10).^2 +((max1 - max3)/max1 * 10).^2);
        matrix_variance_max_percent(j,:,i)=[vari matrix_max_percent(j,:,i)];
    end
end


for i = 1:n
    for j = 1:num_rows
        row = stats(j, :, i);
        max_value = max(row);
        total = sum(row);
        if total ==0
            tot = 1;
        else
            tot = total;
        end
        ratio = round(max_value/tot*100);
   
        matrix_total_variance_ratio_max_percent(j,:,i)=[total matrix_variance_max_percent(j,1,i) ratio matrix_variance_max_percent(j,2:end,i)];
    end
end


matrix_good_total_variance_ratio_max = [];
for i = 1:n
    for j = 1:num_rows
        total = matrix_total_variance_ratio_max_percent(j,1,i);   
        vari = matrix_total_variance_ratio_max_percent(j,2,i);        
        ratio = matrix_total_variance_ratio_max_percent(j,3,i);     
        max_value = matrix_total_variance_ratio_max_percent(j,4,i);
        good = 0;
        if vari>101 & ratio > 60
            good = 1;
        end
          
        matrix_good_total_variance_ratio_max(j,:,i)=[good total vari ratio max_value];
    end
end

result_clusters_valid_validcount_variance_ratio = [];
[m,n] = size(Ck);
temp1 = matrix_good_total_variance_ratio_max;
for i = 1:n
    num_clusters = i + 4;
    toadd = [];
    for j = 1:num_rows
        if temp1(j,1,i) == 1
            toadd = [toadd j];
        end
    end
    temp = [];
    for k = 1:length(toadd)
        temp(k,:,i)= temp1(toadd(k),:,i);
    end
    
    result_clusters_valid_validcount_variance_ratio(i,:) = [num_clusters sum(temp(:,1:2,i)) mean(temp(:,3:4,i))];

end



temp = sum(stats(:,:,2));
row_total = [sum(temp(1:3)) sum(temp(5:7)) sum(temp(9:10)) sum(temp(12:13)) sum(temp(15:17))];
row_total(5) = row_total(5)*2;

cumulating_clusters_total_good_ratio_vari_row_percent = [];
for i = 1:n
    for j = 1:num_rows
        row = stats(j, :, i);
        
        sum1 = sum(row(1:3));
        sum2 = sum(row(5:7));
        sum3 = sum(row(9:10));
        sum4 = sum(row(12:13));
        sum5 = sum(row(15:17));

        row =[sum1 sum2 sum3 sum4 sum5];       %% row %%

        row1 = row;
        [max1,max_idx] = max(row1);
        row1(max_idx) =[];
        [max2,max_idx] = max(row1);
        row1(max_idx) =[];
        [max3,max_idx] = max(row1);
        row1(max_idx) =[];

        if max1==0
            max1 = 1;
        end     
        vari = round(((max1 - max2)/max1 * 10).^2 +((max1 - max3)/max1 * 10).^2);        %% vari %%

        
        max_value = max(row);      
        total = sum(row);        %% total %%
        if total ==0
            tot = 1;
        else
            tot = total;
        end
        ratio = round(max_value/tot*100);        %% ratio %%
        
        good = 0;
        if vari>101 & ratio > 60
            good = 1;
        end
        
        percent = round(row ./ row_total*100);
        
        i_r = find(row==(max(row)));
        i_r = i_r(1);
        i_p = find(percent==(max(percent)));        
        i_p = i_p(1);
        
        cumulating_clusters_total_good_ratio_vari_row_percent(j,:,i)=[j total good ratio vari row i_r i_p percent];        

    end
end



cumulating_clusters_valid_validcount_variance_ratio = [];
[m,n] = size(Ck);
temp1 = cumulating_clusters_total_good_ratio_vari_row_percent;
for i = 1:n
    num_clusters = i + 4;
    toadd = [];
    for j = 1:num_rows
        if temp1(j,3,i) == 1
            toadd = [toadd j];
        end
    end
    temp = [];
    for k = 1:length(toadd)
        temp(k,:,i)= temp1(toadd(k),:,i);
    end
    
    cumulating_clusters_valid_validcount_variance_ratio(i,:) = [num_clusters sum(temp(:,[3,2],i)) mean(temp(:,[5,4],i))];
end

mapping = cumulating_clusters_total_good_ratio_vari_row_percent(:,:,13);

i_zeros = find(mapping(:,2)==0);
mapping(i_zeros,:) = [];
mapping(:,[2 4:10 13:17]) = [];

label_after = Ck(:,13);

for i = 1:length(label_after)
    if mapping(label_after(i),2) == 1
        label_after(i) = mapping(label_after(i),4);
    elseif mapping(label_after(i),2) == 0
        label_after(i) = floor(label(i)/10);
    end
end
    
h1 = histc(floor(label/10),unique(floor(label/10)))
h2 = histc(label_after,unique(label_after))
csvwrite('label_after.csv',label_after);

toc